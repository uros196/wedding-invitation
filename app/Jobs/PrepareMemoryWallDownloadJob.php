<?php

namespace App\Jobs;

use App\Enums\MemoryWallDownloadStatus;
use App\Events\MemoryWallDownloadUpdated;
use App\Models\MemoryWallDownload;
use App\Services\MemoryWall\PrepareMemoryWallArchive;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Builds one immutable download snapshot outside the web request.
 */
final class PrepareMemoryWallDownloadJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 7200;

    public int $uniqueFor = 7200;

    /**
     * Create a job for the archive request that should be prepared.
     */
    public function __construct(public MemoryWallDownload $download) {}

    /**
     * Delegate archive creation to the service that owns ZIP and storage logic.
     */
    public function handle(PrepareMemoryWallArchive $prepareArchive): void
    {
        $prepareArchive->handle($this->download);
    }

    /**
     * Retry archive preparation with short, increasing delays.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Use the snapshot UUID to prevent the same archive being prepared twice.
     */
    public function uniqueId(): string
    {
        return $this->download->uuid;
    }

    /**
     * Mark the request as failed and notify the panel when all retries are spent.
     */
    public function failed(?Throwable $exception): void
    {
        $download = $this->download->fresh();
        if ($download === null || $download->status->isReady() || $download->status->is(MemoryWallDownloadStatus::Cancelled)) {
            return;
        }

        if ($download->status->is(MemoryWallDownloadStatus::Cancelling)) {
            $download->forceFill([
                'status' => MemoryWallDownloadStatus::Cancelled,
                'cancelled_at' => now(),
                'error_message' => $exception?->getMessage(),
            ])->saveQuietly();

            try {
                MemoryWallDownloadUpdated::dispatch($download->fresh() ?? $download);
            } catch (Throwable $broadcastException) {
                report($broadcastException);
            }

            return;
        }

        $download->forceFill([
            'status' => MemoryWallDownloadStatus::Failed,
            'error_message' => $exception?->getMessage(),
        ])->saveQuietly();

        try {
            MemoryWallDownloadUpdated::dispatch($download->fresh() ?? $download);
        } catch (Throwable $broadcastException) {
            report($broadcastException);
        }
    }
}
