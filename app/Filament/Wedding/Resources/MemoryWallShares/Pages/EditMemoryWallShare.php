<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallShares\Pages;

use App\Filament\Wedding\Resources\MemoryWallShares\MemoryWallShareResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemoryWallShare extends EditRecord
{
    protected static string $resource = MemoryWallShareResource::class;

    /**
     * Clear a stored password only when the explicit removal control is enabled.
     *
     * An empty password field otherwise leaves the existing hash unchanged.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($this->data['clear_password'] ?? false) === true && blank($data['password'] ?? null)) {
            $data['password'] = null;
        }

        unset($data['wedding_id'], $data['uuid']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
