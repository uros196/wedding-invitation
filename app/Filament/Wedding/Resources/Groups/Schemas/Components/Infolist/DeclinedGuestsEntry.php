<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use App\Enums\GuestStatus;
use App\Models\Group;
use Filament\Infolists\Components\TextEntry;

class DeclinedGuestsEntry
{
    /**
     * Generate the declined guests count entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('declined_guests')
            ->label(__('Declined'))
            ->state(fn (Group $record): int => $record->guests
                ->where('status', GuestStatus::Declined)
                ->count())
            ->badge()
            ->color('danger');
    }
}
