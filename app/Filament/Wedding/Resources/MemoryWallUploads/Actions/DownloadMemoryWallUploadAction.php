<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Actions;

use App\Models\MemoryWallUpload;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Creates a direct download action for one completed original media object.
 */
final class DownloadMemoryWallUploadAction
{
    /**
     * Create the download memory wall upload action.
     */
    public static function make(): Action
    {
        return Action::make('download')
            ->label(__('Download'))
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->iconButton()
            ->visible(fn (MemoryWallUpload $record): bool => $record->status->isCompleted() && $record->media !== null)
            ->url(fn (MemoryWallUpload $record): string => route('memory-wall.download.single', [
                'wedding' => $record->wedding,
                'memoryWallUpload' => $record,
            ]))
            ->openUrlInNewTab();
    }
}
