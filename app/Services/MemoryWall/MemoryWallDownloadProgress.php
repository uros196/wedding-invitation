<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Models\MemoryWallDownload;

/**
 * Persists throttled byte progress and notifies the wedding panel.
 */
final class MemoryWallDownloadProgress
{
    private int $processedBytes;

    private int $processedFiles = 0;

    private int $lastReportedBytes;

    /**
     * Start progress tracking from the values persisted on the download.
     */
    public function __construct(
        private readonly MemoryWallDownload $download,
        private readonly MemoryWallDownloadNotifier $notifier,
    ) {
        $this->processedBytes = $download->processed_bytes;
        $this->lastReportedBytes = $this->processedBytes;
    }

    /**
     * Accumulate copied bytes and broadcast only after the configured threshold.
     */
    public function addBytes(int $bytes): void
    {
        $this->processedBytes += $bytes;
        $interval = (int) config('memory-wall.download_progress_bytes', 8 * 1024 * 1024);

        if (($this->processedBytes - $this->lastReportedBytes) < $interval) {
            return;
        }

        $this->report();
    }

    /**
     * Publish a file-level update even when the byte threshold was not reached.
     */
    public function fileCompleted(): void
    {
        $this->processedFiles++;
        $this->report();
    }

    /**
     * Persist the exact final totals after the ZIP writer has finished.
     */
    public function finish(): void
    {
        $this->processedBytes = $this->download->total_bytes;
        $this->processedFiles = $this->download->total_files;
        $this->report();
    }

    /**
     * Persist progress quietly and broadcast the refreshed snapshot.
     */
    private function report(): void
    {
        $this->download->forceFill([
            'processed_bytes' => $this->processedBytes,
            'processed_files' => $this->processedFiles,
        ])->saveQuietly();
        $this->lastReportedBytes = $this->processedBytes;

        $this->notifier->notify($this->download->fresh() ?? $this->download);
    }
}
