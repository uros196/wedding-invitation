<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Actions;

use App\Models\User;
use App\Models\Wedding;
use App\Services\MemoryWall\CreateMemoryWallDownload;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use RuntimeException;

/**
 * Starts an asynchronous archive of all completed memory wall files.
 */
final class DownloadMemoryWallArchiveAction
{
    /**
     * Configure the toolbar action that starts asynchronous archive creation.
     */
    public static function make(): Action
    {
        return Action::make('downloadAll')
            ->label(__('wedding.memory_wall.download.all'))
            ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
            ->requiresConfirmation()
            ->action(function (CreateMemoryWallDownload $createDownload): void {
                $user = auth()->user();
                if (! $user instanceof User) {
                    abort(403);
                }

                $wedding = $user->team?->wedding;
                if (! $wedding instanceof Wedding) {
                    abort(403);
                }

                try {
                    $download = $createDownload->handle($wedding, $user);
                } catch (RuntimeException $exception) {
                    Notification::make()
                        ->title($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(__('wedding.memory_wall.download.started'))
                    ->body(__('wedding.memory_wall.download.started_body'))
                    ->success()
                    ->send();
            });
    }
}
