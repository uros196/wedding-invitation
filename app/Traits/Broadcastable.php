<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification;

trait Broadcastable
{
    /**
     * Broadcasts the given notification to the provided notifiable user and manually
     * triggers the database notification to ensure proper handling of notification events.
     */
    protected function inform(User $notifiable, Notification $notification): void
    {
        // Broadcast the notification.
        // For some strange reason if 'toBroadcast' is enabled,
        // DB notification won't send to the database. So we
        // do broadcast manual.
        $notification->broadcast($notifiable);

        // Dispatch the event so notification bar ca be properly
        // notified that something changed in the DB.
//        DatabaseNotificationsSent::dispatch($notifiable);
    }
}
