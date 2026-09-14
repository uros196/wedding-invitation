<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Enums\MemoryWallDownloadStatus;
use App\Models\MemoryWallDownload;
use Illuminate\Support\Facades\DB;

/**
 * Requests cooperative cancellation without killing a running queue worker.
 */
final readonly class CancelMemoryWallDownload
{
    /**
     * Notify the panel after a lifecycle transition has been persisted.
     */
    public function __construct(private MemoryWallDownloadNotifier $notifier) {}

    /**
     * Cancel queued work immediately or ask an active worker to stop safely.
     */
    public function handle(MemoryWallDownload $download): void
    {
        $updatedDownload = DB::transaction(function () use ($download): ?MemoryWallDownload {
            /** @var MemoryWallDownload|null $lockedDownload */
            $lockedDownload = MemoryWallDownload::query()
                ->lockForUpdate()
                ->find($download->getKey());

            if ($lockedDownload === null) {
                return null;
            }

            return match ($lockedDownload->status) {
                MemoryWallDownloadStatus::Queued => $this->markQueuedAsCancelled($lockedDownload),
                MemoryWallDownloadStatus::Processing => $this->markProcessingAsCancelling($lockedDownload),
                default => null,
            };
        });

        if ($updatedDownload !== null) {
            $this->notifier->notify($updatedDownload->fresh() ?? $updatedDownload);
        }
    }

    /**
     * A queued job has not created an S3 multipart upload yet.
     */
    private function markQueuedAsCancelled(MemoryWallDownload $download): MemoryWallDownload
    {
        $download->forceFill([
            'status' => MemoryWallDownloadStatus::Cancelled,
            'archive_path' => null,
            'multipart_upload_id' => null,
            'cancellation_requested_at' => now(),
            'cancelled_at' => now(),
        ])->saveQuietly();

        return $download;
    }

    /**
     * Let the worker abort its own multipart upload at the next safe boundary.
     */
    private function markProcessingAsCancelling(MemoryWallDownload $download): MemoryWallDownload
    {
        $download->forceFill([
            'status' => MemoryWallDownloadStatus::Cancelling,
            'cancellation_requested_at' => now(),
        ])->saveQuietly();

        return $download;
    }
}
