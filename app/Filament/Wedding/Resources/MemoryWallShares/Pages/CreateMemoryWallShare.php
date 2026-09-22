<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallShares\Pages;

use App\Filament\Wedding\Resources\MemoryWallShares\MemoryWallShareResource;
use App\Models\Wedding;
use Filament\Resources\Pages\CreateRecord;

class CreateMemoryWallShare extends CreateRecord
{
    protected static string $resource = MemoryWallShareResource::class;

    /**
     * Bind every new share to the wedding owned by the authenticated team.
     *
     * The wedding identifier is deliberately never accepted from form state.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $wedding = auth()->user()?->team?->wedding;

        abort_unless($wedding instanceof Wedding, 403);

        $data['wedding_id'] = $wedding->getKey();

        return $data;
    }
}
