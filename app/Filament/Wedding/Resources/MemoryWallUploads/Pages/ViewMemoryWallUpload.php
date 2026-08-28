<?php

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Pages;

use App\Filament\Wedding\Resources\MemoryWallUploads\MemoryWallUploadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

/**
 * Displays one upload and provides the panel cleanup action.
 */
class ViewMemoryWallUpload extends ViewRecord
{
    protected static string $resource = MemoryWallUploadResource::class;

    /**
     * Allow wedding users to remove the selected media from the panel.
     *
     * MemoryWallUpload's deleting hook also removes the related media record.
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
