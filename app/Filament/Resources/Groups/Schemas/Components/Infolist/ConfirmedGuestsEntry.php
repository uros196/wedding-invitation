<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use App\Enums\GuestStatus;
use App\Models\Group;
use Filament\Infolists\Components\TextEntry;

class ConfirmedGuestsEntry
{
    /**
     * Generate the confirmed guests count entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('confirmed_guests')
            ->label(__('Confirmed'))
            ->state(fn (Group $record): int => $record->guests
                ->where('status', GuestStatus::Confirmed)
                ->count())
            ->badge()
            ->color('success');
    }
}
