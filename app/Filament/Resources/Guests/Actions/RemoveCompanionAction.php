<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guests\Actions;

use App\Models\Guest;
use App\Services\GuestService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class RemoveCompanionAction
{
    /**
     * Generates the action to remove a companion from the list.
     * This action unassigns the companion by setting parent_id to null.
     */
    public static function make(?string $name = null): Action
    {
        return Action::make($name ?? 'removeCompanion')
            ->label('Ukloni')
            ->icon(Heroicon::XMark)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Ukloni kompanjona')
            ->modalDescription('Da li ste sigurni da želite da uklonite ovog kompanjona iz liste?')
            ->modalSubmitActionLabel('Ukloni')
            ->action(function (Guest $record) {
                $parent = $record->parent;

                resolve(GuestService::class)->freeCompanionFromParent($record);

                // Refresh list on the page
                $parent?->refresh();
            });
    }
}
