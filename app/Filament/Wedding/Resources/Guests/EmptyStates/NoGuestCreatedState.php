<?php

namespace App\Filament\Wedding\Resources\Guests\EmptyStates;

use App\Models\Guest;
use Filament\Schemas\Components\EmptyState;
use Filament\Support\Icons\Heroicon;

class NoGuestCreatedState
{
    /**
     * Make an empty state for the guest section.
     *
     * This section will be visible when the guest record does not exist.
     */
    public static function make(): EmptyState
    {
        return EmptyState::make('No content')
            ->heading(__('No guest information'))
            ->description(__('messages.create_guest_description'))
            ->contained(false)
            ->icon(Heroicon::UserCircle)
            ->visible(fn (?Guest $record) => is_null($record));
    }
}
