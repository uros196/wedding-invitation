<?php

namespace App\Filament\Wedding\Resources\MemoryWallShares\Pages;

use App\Filament\Wedding\Resources\MemoryWallShares\MemoryWallShareResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemoryWallShares extends ListRecords
{
    protected static string $resource = MemoryWallShareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
