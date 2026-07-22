<?php

namespace App\Filament\Wedding\Resources\Guests\Pages;

use App\Filament\Wedding\Resources\Guests\GuestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuest extends CreateRecord
{
    protected static string $resource = GuestResource::class;
}
