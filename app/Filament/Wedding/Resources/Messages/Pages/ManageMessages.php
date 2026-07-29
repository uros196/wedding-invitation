<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Messages\Pages;

use App\Filament\Wedding\Resources\Messages\MessageResource;
use App\Traits\TableRefreshable;
use Filament\Resources\Pages\ManageRecords;

class ManageMessages extends ManageRecords
{
    use TableRefreshable;

    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    /**
     * Refresh the table when this broadcast event occurs.
     */
    protected function refreshTableOn(): string|array
    {
        return '.messageReceived';
    }
}
