<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Group;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Message $message,
        public readonly Group $group,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * The wedding channel keeps message updates isolated to users who belong
     * to the wedding that owns the newly created message.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->group->team->broadcastChannelName())];
    }

    /**
     * Get the frontend event name.
     */
    public function broadcastAs(): string
    {
        return 'messageReceived';
    }

    /**
     * Limit the broadcast payload to the identifier needed by the listener.
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->getKey(),
        ];
    }
}
