<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Actions;

use App\Models\User;
use App\Models\Wedding;
use App\Services\MemoryWall\CreateMemoryWallDownload;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * Starts an asynchronous archive containing only the selected memory wall files.
 */
final class DownloadSelectedMemoryWallArchiveAction
{
    /**
     * Configure the bulk action that starts asynchronous archive creation.
     */
    public static function make(): BulkAction
    {
        return BulkAction::make('downloadSelected')
            ->label(__('wedding.memory_wall.download.selected'))
            ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, CreateMemoryWallDownload $createDownload): void {
                $user = auth()->user();
                if (! $user instanceof User) {
                    abort(403);
                }

                $wedding = $user->team?->wedding;
                if (! $wedding instanceof Wedding) {
                    abort(403);
                }

                try {
                    $createDownload->handle(
                        $wedding,
                        $user,
                        array_map(static fn (mixed $id): int => (int) $id, $records->modelKeys()),
                    );
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
