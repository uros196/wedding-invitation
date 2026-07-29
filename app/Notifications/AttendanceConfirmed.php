<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Filament\Wedding\Resources\Groups\Pages\EditGroup;
use App\Models\Group;
use App\Models\User;
use App\Traits\Broadcastable;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AttendanceConfirmed extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public function __construct(
        public Group $group,
        public int $confirmedCount,
        public int $totalCount
    ) {}

    /**
     * Define notification channels.
     */
    public function via(User $notifiable): array
    {
        return ['database', /*'broadcast'*/];
    }

    /**
     * Create a broadcast notification.
     */
    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        return $this->makeNotification()
            ->getBroadcastMessage();
    }

    /**
     * Create a database notification.
     */
    public function toDatabase(User $notifiable): array
    {
        $notification = $this->makeNotification();

        // Manual broadcast notification
        $this->inform($notifiable, $notification);

        return $notification
            ->actions([
                Action::make('view')
                    ->label(__('View Group'))
                    ->url(EditGroup::getUrl(['record' => $this->group->id])),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Compose a notification specific to attendance confirmation.
     */
    protected function makeNotification(): FilamentNotification
    {
        return FilamentNotification::make()
            ->title(__('Attendance Confirmed'))
            ->body(__('notification.guest_confirmation', [
                'count' => $this->confirmedCount,
                'total' => $this->totalCount,
                'group' => $this->group->name,
            ]))
            ->success();
    }
}
