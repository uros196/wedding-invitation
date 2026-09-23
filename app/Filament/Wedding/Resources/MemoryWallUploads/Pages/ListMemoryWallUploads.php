<?php

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Pages;

use App\Filament\Wedding\Pages\ManageWedding\ManageWedding;
use App\Filament\Wedding\Resources\MemoryWallUploads\MemoryWallUploadResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

/**
 * Lists the current wedding's memory wall upload sessions.
 */
class ListMemoryWallUploads extends ListRecords
{
    protected static string $resource = MemoryWallUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('shareMemoryWall')
                ->label(__('Share Memory Wall'))
                ->icon(Heroicon::Share)
                ->url(ManageWedding::getUrl(['tab' => 'memory'])),
        ];
    }
}
