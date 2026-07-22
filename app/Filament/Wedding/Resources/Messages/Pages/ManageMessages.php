<?php

namespace App\Filament\Wedding\Resources\Messages\Pages;

use App\Filament\Wedding\Resources\Messages\MessageResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMessages extends ManageRecords
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
