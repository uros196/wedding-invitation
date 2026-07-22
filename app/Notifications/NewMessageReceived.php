<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Filament\Wedding\Resources\Messages\MessageResource;
use App\Models\Group;
use App\Models\Message;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class NewMessageReceived extends Notification
{
    public function __construct(
        public Group $group,
        public Message $message
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
            ->title(__('notification.new_message', ['group' => $this->group->name]))
            ->body($this->message->content)
            ->info()
            ->actions([
                Action::make('view')
                    ->label(__('View Message'))
                    ->url(MessageResource::getUrl('view', ['record' => $this->message->id])),
            ])
            ->getDatabaseMessage();
    }
}
