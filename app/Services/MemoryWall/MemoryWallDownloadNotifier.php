<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Events\MemoryWallDownloadUpdated;
use App\Models\MemoryWallDownload;
use Throwable;

/**
 * Publishes download changes without making websocket availability a job dependency.
 */
final class MemoryWallDownloadNotifier
{
    /**
     * Broadcast the latest state while allowing archive processing to continue
     * if the websocket transport is unavailable.
     */
    public function notify(MemoryWallDownload $download): void
    {
        try {
            MemoryWallDownloadUpdated::dispatch($download);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
