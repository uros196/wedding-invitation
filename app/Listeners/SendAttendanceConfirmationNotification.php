<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AttendanceConfirmed;
use App\Notifications\AttendanceConfirmed as AttendanceConfirmedNotification;
use Illuminate\Support\Facades\Notification;

class SendAttendanceConfirmationNotification
{
    /**
     * Handle the event.
     */
    public function handle(AttendanceConfirmed $event): void
    {
        // Send notification to admins
        $admins = $event->group->wedding->users()->get();

        $notification = new AttendanceConfirmedNotification(
            group: $event->group,
            confirmedCount: count($event->confirmedIds),
            totalCount: $event->group->guests->count()
        );

        Notification::send($admins, $notification);
    }
}
