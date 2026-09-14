<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\MemoryWallDownload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcasts archive preparation progress to the wedding panel.
 */
final class MemoryWallDownloadUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create an event for the current state of one archive request.
     */
    public function __construct(public readonly MemoryWallDownload $download) {}

    /**
     * Send progress only to the team that owns the wedding.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->download->wedding->team->broadcastChannelName())];
    }

    /**
     * Get the stable frontend event name used by Echo.
     */
    public function broadcastAs(): string
    {
        return 'memoryWallDownloadUpdated';
    }

    /**
     * Expose only the progress fields needed by the download manager.
     *
     * @return array{
     *     download_uuid: string,
     *     status: string,
     *     total_bytes: int,
     *     processed_bytes: int,
     *     total_files: int,
     *     processed_files: int,
     *     progress: int,
     * }
     */
    public function broadcastWith(): array
    {
        return [
            'download_uuid' => $this->download->uuid,
            'status' => $this->download->status->value,
            'total_bytes' => $this->download->total_bytes,
            'processed_bytes' => $this->download->processed_bytes,
            'total_files' => $this->download->total_files,
            'processed_files' => $this->download->processed_files,
            'progress' => $this->download->progressPercentage(),
        ];
    }
}
