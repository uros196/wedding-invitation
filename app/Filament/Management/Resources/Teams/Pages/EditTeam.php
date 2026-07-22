<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Teams\Pages;

use App\Filament\Management\Resources\Teams\TeamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Provides the team editing page.
 */
class EditTeam extends EditRecord
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
