<?php

namespace App\Filament\Wedding\Resources\Guests\EmptyStates;

use App\Filament\Wedding\Resources\Guests\Actions\AddCompanionAction;
use App\Models\Guest;
use Filament\Schemas\Components\EmptyState;
use Filament\Support\Icons\Heroicon;

class NoCompanionAddedState
{
    /**
     * Make an empty state for the companion section.
     *
     * This section will be visible when the guest record exists but without companions.
     */
    public static function make(): EmptyState
    {
        $isVisible = fn (?Guest $record) => $record?->exists && ! $record->hasCompanions();

        return EmptyState::make('No content')
            ->heading(__('wedding.guests.companions.empty'))
            ->description(__('wedding.guests.companions.add_description'))
            ->contained(false)
            ->icon(Heroicon::UserCircle)
            ->footer([
                AddCompanionAction::make()
                    ->visible($isVisible),
            ])
            ->visible($isVisible);
    }
}
