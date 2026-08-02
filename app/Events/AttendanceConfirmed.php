<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Group;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AttendanceConfirmed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected readonly string $broadcastChannel;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Group $group,
        public readonly array $confirmedIds,
    ) {
        $this->broadcastChannel = $group->team->broadcastChannelName();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->broadcastChannel)];
    }

    /**
     * Get the frontend event name.
     */
    public function broadcastAs(): string
    {
        return 'attendanceConfirmed';
    }

    /**
     * Limit the broadcast payload to the identifier needed by the listener.
     */
    public function broadcastWith(): array
    {
        return [
            'group_id' => $this->group->getKey(),
            'confirmed_ids' => $this->confirmedIds,
        ];
    }
}
