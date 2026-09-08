<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\FilamentPanel;
use App\Filament\Wedding\Resources\MemoryWallUploads\MemoryWallUploadResource;
use App\Models\User;
use App\Traits\Broadcastable;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * A coalesced summary of completed Memory Wall uploads.
 */
final class MemoryWallUploadDigest extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    /**
     * Create a digest notification with the media totals for one quiet period.
     */
    public function __construct(
        public int $imageCount,
        public int $videoCount,
    ) {}

    /**
     * Define notification channels.
     *
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['database'];
    }

    /**
     * Create a broadcast notification.
     */
    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        return $this->makeNotification()->getBroadcastMessage();
    }

    /**
     * Create a database notification for the Filament notification center.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        $notification = $this->makeNotification();

        // Manual broadcast notification
        $this->inform($notifiable, $notification);

        return [
            ...$notification
                ->actions([
                    Action::make('view')
                        ->label(__('Memory uploads'))
                        ->url(MemoryWallUploadResource::getUrl('index', panel: FilamentPanel::Wedding->id())),
                ])
                ->getDatabaseMessage(),
            'image_count' => $this->imageCount,
            'video_count' => $this->videoCount,
        ];
    }

    /**
     * Build the localized Filament notification content.
     */
    protected function makeNotification(): FilamentNotification
    {
        return FilamentNotification::make()
            ->title(__('wedding.notifications.memory_wall_uploads_title'))
            ->body(__('wedding.notifications.memory_wall_uploads', [
                'images' => $this->imageCount,
                'videos' => $this->videoCount,
            ]))
            ->info();
    }
}
