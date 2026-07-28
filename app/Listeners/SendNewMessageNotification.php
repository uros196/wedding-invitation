<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MessageReceived;
use App\Notifications\NewMessageReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendNewMessageNotification
{
    /**
     * Handle the event.
     */
    public function handle(MessageReceived $event): void
    {
        // Send notification to admins
        $admins = $event->group->wedding->users()->get();

        Notification::send($admins, new NewMessageReceived($event->group, $event->message));
    }
}
