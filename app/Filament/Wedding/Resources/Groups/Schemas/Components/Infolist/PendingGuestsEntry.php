<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use App\Enums\GuestStatus;
use App\Models\Group;
use Filament\Infolists\Components\TextEntry;

class PendingGuestsEntry
{
    /**
     * Generate the pending guests count entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('pending_guests')
            ->label(__('Pending'))
            ->state(fn (Group $record): int => $record->guests
                ->where('status', GuestStatus::Pending)
                ->count())
            ->badge()
            ->color('gray');
    }
}
