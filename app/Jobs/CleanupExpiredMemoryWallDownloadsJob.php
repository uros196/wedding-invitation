<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\MemoryWallArchiveStorage;
use App\Enums\MemoryWallDownloadStatus;
use App\Models\MemoryWallDownload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Cleans temporary archive objects and abandoned multipart uploads.
 */
final class CleanupExpiredMemoryWallDownloadsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    /**
     * Retry cleanup with increasing delays when storage is temporarily unavailable.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    /**
     * Delete expired, cancelled, and abandoned archive objects in bounded batches.
     */
    public function handle(MemoryWallArchiveStorage $storage): void
    {
        $now = now();

        MemoryWallDownload::query()
            ->select([
                'id',
                'status',
                'archive_path',
                'multipart_upload_id',
                'expires_at',
                'cancellation_requested_at',
                'cancelled_at',
            ])
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $query) use ($now): void {
                    $query->where('status', MemoryWallDownloadStatus::Ready)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<=', $now);
                })->orWhere(function (Builder $query): void {
                    $query->whereIn('status', [
                        MemoryWallDownloadStatus::Cancelled,
                        MemoryWallDownloadStatus::Expired,
                        MemoryWallDownloadStatus::Failed,
                    ])->where(function (Builder $query): void {
                        $query->whereNotNull('archive_path')
                            ->orWhereNotNull('multipart_upload_id');
                    });
                })->orWhere(function (Builder $query) use ($now): void {
                    $query->where('status', MemoryWallDownloadStatus::Cancelling)
                        ->whereNotNull('cancellation_requested_at')
                        ->where('cancellation_requested_at', '<=', $now->copy()->subDay());
                });
            })
            ->chunkById(100, function (Collection $downloads) use ($storage): void {
                foreach ($downloads as $download) {
                    $this->cleanupDownload($download, $storage);
                }
            });
    }

    /**
     * Release both a completed archive and any persisted multipart upload.
     */
    private function cleanupDownload(MemoryWallDownload $download, MemoryWallArchiveStorage $storage): void
    {
        if (filled($download->multipart_upload_id) && filled($download->archive_path)) {
            $storage->abortMultipartUpload($download->archive_path, $download->multipart_upload_id);
        }

        if (filled($download->archive_path)) {
            $storage->deleteObject($download->archive_path);
        }

        $status = match ($download->status) {
            MemoryWallDownloadStatus::Ready => MemoryWallDownloadStatus::Expired,
            MemoryWallDownloadStatus::Cancelling => MemoryWallDownloadStatus::Cancelled,
            default => $download->status,
        };

        $download->forceFill([
            'status' => $status,
            'archive_path' => null,
            'multipart_upload_id' => null,
            'cancelled_at' => $status->is(MemoryWallDownloadStatus::Cancelled)
                ? ($download->cancelled_at ?? now())
                : $download->cancelled_at,
        ])->saveQuietly();
    }

    /**
     * Report a terminal cleanup failure instead of leaving it silent.
     */
    public function failed(?Throwable $exception): void
    {
        report($exception);
    }
}
