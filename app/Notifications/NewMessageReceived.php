<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Filament\Wedding\Resources\Messages\MessageResource;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use App\Traits\Broadcastable;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewMessageReceived extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public function __construct(
        public Group $group,
        public Message $message
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
    public function toBroadcast(): BroadcastMessage
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

        $data = $notification
            ->actions([
                Action::make('view')
                    ->label(__('View Message'))
                    ->url(MessageResource::getUrl('view', ['record' => $this->message->id])),
            ])
            ->getDatabaseMessage();

        return [
            ...$data,
            'message_id' => $this->message->id,
        ];
    }

    /**
     * Build and return a notification instance.
     */
    protected function makeNotification(): FilamentNotification
    {
        return FilamentNotification::make()
            ->title(__('notification.new_message', ['group' => $this->group->name]))
            ->body($this->message->content)
            ->info();
    }
}
