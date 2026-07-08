<?php

namespace App\Filament\Resources\Guests\EmptyStates;

use App\Filament\Resources\Guests\Actions\AddCompanionAction;
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
            ->description('Dodajte posetioce koji dolaze sa ovim gostom.')
            ->contained(false)
            ->icon(Heroicon::UserCircle)
            ->footer([
                AddCompanionAction::make()
                    ->visible($isVisible),
            ])
            ->visible($isVisible);
    }
}
