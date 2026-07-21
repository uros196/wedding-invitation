<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

class GuestsEntry
{
    /**
     * Generate the guests list entry.
     */
    public static function make(): RepeatableEntry
    {
        return RepeatableEntry::make('guests')
            ->label(__('Guests'))
            ->schema([
                TextEntry::make('full_name')
                    ->label(__('Full name')),
                GuestAgeEntry::make(),
                GuestGenderEntry::make(),
                GuestStatusEntry::make(),
                GuestNotesEntry::make(),
            ])
            ->columns(4)
            ->columnSpanFull();
    }
}
