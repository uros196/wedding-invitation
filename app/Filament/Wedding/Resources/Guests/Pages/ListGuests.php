<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Guests\Pages;

use App\Filament\Wedding\Resources\Guests\GuestResource;
use App\Traits\TableRefreshable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuests extends ListRecords
{
    use TableRefreshable;

    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * Refresh the table when this broadcast event occurs.
     */
    protected function refreshTableOn(): string|array
    {
        return '.attendanceConfirmed';
    }
}
