<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Guests\Pages;

use App\Filament\Wedding\Resources\Guests\GuestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGuest extends ViewRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
