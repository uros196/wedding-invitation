<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use App\Models\Group;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;

class GuestsCountEntry
{
    /**
     * Generate the guests count entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('guests_count')
            ->label(__('Guests'))
            ->state(fn (Group $record): int => $record->guests->count())
            ->numeric()
            ->icon(Heroicon::OutlinedUsers);
    }
}
