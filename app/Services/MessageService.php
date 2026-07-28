<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageReceived;

class MessageService
{
    /**
     * Get the unread message count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()
            ->where('type', NewMessageReceived::class)
            ->count();
    }

    /**
     * Mark a message as read for a user.
     */
    public function markAsRead(User $user, Message $message): void
    {
        $user->unreadNotifications()
            ->where('type', NewMessageReceived::class)
            ->where('data->message_id', $message->id)
            ->first()
            ?->markAsRead();
    }
}
