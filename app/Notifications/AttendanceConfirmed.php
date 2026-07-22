<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Filament\Wedding\Resources\Groups\Pages\EditGroup;
use App\Models\Group;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class AttendanceConfirmed extends Notification
{
    public function __construct(
        public Group $group,
        public int $confirmedCount,
        public int $totalCount
    ) {}

    /**
     * Define notification channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Create a database notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('Attendance Confirmed'))
            ->body(__('notification.guest_confirmation', [
                'count' => $this->confirmedCount,
                'total' => $this->totalCount,
                'group' => $this->group->name,
            ]))
            ->success()
            ->actions([
                Action::make('view')
                    ->label(__('View Group'))
                    ->url(EditGroup::getUrl(['record' => $this->group->id])),
            ])
            ->getDatabaseMessage();
    }
}
