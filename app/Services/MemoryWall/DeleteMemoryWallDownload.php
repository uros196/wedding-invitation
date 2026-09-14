<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Contracts\MemoryWallArchiveStorage;
use App\Enums\MemoryWallDownloadStatus;
use App\Models\MemoryWallDownload;
use Illuminate\Support\Facades\DB;

/**
 * Removes a completed archive object while keeping a terminal audit record.
 */
final readonly class DeleteMemoryWallDownload
{
    /**
     * Inject storage and the broadcaster used by the download manager.
     */
    public function __construct(
        private MemoryWallArchiveStorage $storage,
        private MemoryWallDownloadNotifier $notifier,
    ) {}

    /**
     * Delete a ready or otherwise terminal archive in an idempotent way.
     */
    public function handle(MemoryWallDownload $download): void
    {
        $updatedDownload = DB::transaction(function () use ($download): ?MemoryWallDownload {
            /** @var MemoryWallDownload|null $lockedDownload */
            $lockedDownload = MemoryWallDownload::query()
                ->lockForUpdate()
                ->find($download->getKey());

            if ($lockedDownload === null || ! $lockedDownload->status->canBeRemoved()) {
                return null;
            }

            if (filled($lockedDownload->archive_path)) {
                $this->storage->deleteObject($lockedDownload->archive_path);
            }

            $lockedDownload->forceFill([
                'status' => MemoryWallDownloadStatus::Cancelled,
                'archive_path' => null,
                'multipart_upload_id' => null,
                'cancellation_requested_at' => $lockedDownload->cancellation_requested_at ?? now(),
                'cancelled_at' => now(),
            ])->saveQuietly();

            return $lockedDownload;
        });

        if ($updatedDownload !== null) {
            $this->notifier->notify($updatedDownload->fresh() ?? $updatedDownload);
        }
    }
}
