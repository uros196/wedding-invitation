<?php

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Pages;

use App\Filament\Wedding\Resources\MemoryWallUploads\MemoryWallUploadResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Lists the current wedding's memory wall upload sessions.
 */
class ListMemoryWallUploads extends ListRecords
{
    protected static string $resource = MemoryWallUploadResource::class;
}
