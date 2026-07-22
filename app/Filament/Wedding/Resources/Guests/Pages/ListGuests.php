<?php

namespace App\Filament\Wedding\Resources\Guests\Pages;

use App\Filament\Wedding\Resources\Guests\GuestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuests extends ListRecords
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
