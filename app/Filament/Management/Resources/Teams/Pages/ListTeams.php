<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Teams\Pages;

use App\Filament\Management\Resources\Teams\TeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * Provides the team listing page.
 */
class ListTeams extends ListRecords
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
