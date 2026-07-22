<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class GuestAgeEntry
{
    /**
     * Generate the guest age entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('age')
            ->label(__('Age'))
            ->badge();
    }
}
