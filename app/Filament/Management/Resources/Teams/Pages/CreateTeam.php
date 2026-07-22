<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Teams\Pages;

use App\Filament\Management\Resources\Teams\TeamResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Provides the team creation page.
 */
class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;
}
