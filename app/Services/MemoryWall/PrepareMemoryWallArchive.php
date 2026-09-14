<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Contracts\MemoryWallArchiveStorage;
use App\Enums\MemoryWallDownloadStatus;
use App\Exceptions\MemoryWallDownloadCancelled;
use App\Models\MemoryWallDownload;
use App\Models\MemoryWallDownloadItem;
use App\Services\MemoryWall\Archive\MultipartArchiveSink;
use App\Services\MemoryWall\Archive\StreamingZipWriter;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Builds one download snapshot as a ZIP64 object on S3.
 */
final readonly class PrepareMemoryWallArchive
{
    /**
     * Inject storage, source reading, and progress notification collaborators.
     */
    public function __construct(
        private MemoryWallArchiveStorage $storage,
        private FilesystemMemoryWallMediaReader $mediaReader,
        private MemoryWallDownloadNotifier $notifier,
    ) {}

    /**
     * Stream every snapshot item into S3 and move the request through its states.
     */
    public function handle(MemoryWallDownload $download): void
    {
        $download->refresh();
        if (! $this->markAsProcessing($download)) {
            if ($download->status->isCancelling()) {
                $this->markAsCancelled($download, archiveCleanupCompleted: false);
            }

            return;
        }

        $uploadId = null;
        $multipartCompleted = false;

        try {
            $this->throwIfCancellationRequested($download);
            $uploadId = $this->storage->createMultipartUpload($download->archive_path, $download->archive_name);
            $download->forceFill(['multipart_upload_id' => $uploadId])->saveQuietly();
            $this->throwIfCancellationRequested($download);

            $sink = new MultipartArchiveSink(
                $this->storage,
                $download->archive_path,
                $uploadId,
                (int) config('memory-wall.download_part_size', 64 * 1024 * 1024),
                function () use ($download): void {
                    $this->throwIfCancellationRequested($download);
                },
            );
            $writer = new StreamingZipWriter($sink);
            $progress = new MemoryWallDownloadProgress($download, $this->notifier);

            $download->items()
                ->orderBy('id')
                ->cursor()
                ->each(function (MemoryWallDownloadItem $item) use ($download, $writer, $progress): void {
                    $this->throwIfCancellationRequested($download);
                    $writer->addFile(
                        $item->archive_name,
                        fn () => $this->mediaReader->open($item->disk, $item->path),
                        $item->size,
                        fn (int $bytes): null => $progress->addBytes($bytes),
                        function () use ($download): void {
                            $this->throwIfCancellationRequested($download);
                        },
                    );
                    $this->throwIfCancellationRequested($download);
                    $progress->fileCompleted();
                });
            $this->throwIfCancellationRequested($download);
            $writer->finish();
            $this->throwIfCancellationRequested($download);
            $parts = $sink->finish();
            $this->throwIfCancellationRequested($download);
            $this->storage->completeMultipartUpload($download->archive_path, $uploadId, $parts);
            $multipartCompleted = true;
            $this->clearMultipartUploadId($download);
            $progress->finish();
            if (! $this->markAsReady($download)) {
                $this->storage->deleteObject($download->archive_path);
                $this->markAsCancelled($download);
            }
        } catch (MemoryWallDownloadCancelled) {
            if ($uploadId !== null && ! $multipartCompleted) {
                $this->storage->abortMultipartUpload($download->archive_path, $uploadId);
            }

            $this->markAsCancelled($download);
        } catch (Throwable $exception) {
            if ($uploadId !== null && ! $multipartCompleted) {
                $this->storage->abortMultipartUpload($download->archive_path, $uploadId);
            }

            throw $exception;
        }
    }

    /**
     * Move a queued request into processing while respecting a concurrent cancel.
     */
    private function markAsProcessing(MemoryWallDownload $download): bool
    {
        $updated = DB::transaction(function () use ($download): bool {
            /** @var MemoryWallDownload|null $lockedDownload */
            $lockedDownload = MemoryWallDownload::query()
                ->lockForUpdate()
                ->find($download->getKey());

            if ($lockedDownload === null || ! $lockedDownload->status->isPreparing()) {
                return false;
            }

            $lockedDownload->forceFill([
                'status' => MemoryWallDownloadStatus::Processing,
                'started_at' => now(),
                'processed_bytes' => 0,
                'processed_files' => 0,
                'multipart_upload_id' => null,
                'error_message' => null,
            ])->saveQuietly();

            return true;
        });

        if (! $updated) {
            return false;
        }

        $this->notifier->notify($download->fresh() ?? $download);

        return true;
    }

    /**
     * Mark the archive ready only if cancellation did not win the final race.
     */
    private function markAsReady(MemoryWallDownload $download): bool
    {
        $updated = DB::transaction(function () use ($download): bool {
            /** @var MemoryWallDownload|null $lockedDownload */
            $lockedDownload = MemoryWallDownload::query()
                ->lockForUpdate()
                ->find($download->getKey());

            if ($lockedDownload === null || ! $lockedDownload->status->is(MemoryWallDownloadStatus::Processing)) {
                return false;
            }

            $lockedDownload->forceFill([
                'status' => MemoryWallDownloadStatus::Ready,
                'completed_at' => now(),
                'expires_at' => now()->addMinutes((int) config('memory-wall.download_url_minutes', 60)),
                'multipart_upload_id' => null,
            ])->saveQuietly();

            return true;
        });

        if (! $updated) {
            return false;
        }

        $this->notifier->notify($download->fresh() ?? $download);

        return true;
    }

    /**
     * Persist that cancellation cleanup completed and hide the archive from the panel.
     */
    private function markAsCancelled(MemoryWallDownload $download, bool $archiveCleanupCompleted = true): void
    {
        $updatedDownload = DB::transaction(function () use ($download, $archiveCleanupCompleted): ?MemoryWallDownload {
            /** @var MemoryWallDownload|null $lockedDownload */
            $lockedDownload = MemoryWallDownload::query()
                ->lockForUpdate()
                ->find($download->getKey());

            if ($lockedDownload === null) {
                return null;
            }

            $lockedDownload->forceFill([
                'status' => MemoryWallDownloadStatus::Cancelled,
                'archive_path' => $archiveCleanupCompleted ? null : $lockedDownload->archive_path,
                'multipart_upload_id' => $archiveCleanupCompleted ? null : $lockedDownload->multipart_upload_id,
                'cancelled_at' => now(),
            ])->saveQuietly();

            return $lockedDownload;
        });

        if ($updatedDownload !== null) {
            $this->notifier->notify($updatedDownload->fresh() ?? $updatedDownload);
        }
    }

    /**
     * Check the database at every safe source and storage boundary.
     */
    private function throwIfCancellationRequested(MemoryWallDownload $download): void
    {
        $download->refresh();
        $status = $download->status;

        if (in_array(
            $status,
            [MemoryWallDownloadStatus::Cancelling, MemoryWallDownloadStatus::Cancelled],
            true,
        )) {
            throw new MemoryWallDownloadCancelled;
        }
    }

    /**
     * The completed S3 object no longer has an active multipart upload.
     */
    private function clearMultipartUploadId(MemoryWallDownload $download): void
    {
        $download->forceFill(['multipart_upload_id' => null])->saveQuietly();
    }
}
