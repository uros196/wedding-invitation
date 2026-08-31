<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\MemoryWallUploadStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies the guest browser that a queued Memory Wall upload reached a
 * terminal state.
 */
final class MemoryWallUploadProcessed implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new upload processing event.
     *
     * @param  array<string, mixed>|null  $media
     */
    public function __construct(
        public readonly string $uploadUuid,
        public readonly MemoryWallUploadStatus $status,
        public readonly ?array $media = null,
        public readonly ?string $error = null,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel("memory-wall-upload.{$this->uploadUuid}")];
    }

    /**
     * Get the frontend event name.
     */
    public function broadcastAs(): string
    {
        return 'memoryWallUploadProcessed';
    }

    /**
     * Expose only the data needed to update this upload in the browser.
     *
     * @return array{upload_uuid: string, status: string, media: array<string, mixed>|null, error: string|null}
     */
    public function broadcastWith(): array
    {
        return [
            'upload_uuid' => $this->uploadUuid,
            'status' => $this->status->value,
            'media' => $this->media,
            'error' => $this->error,
        ];
    }
}
