<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class GuestGenderEntry
{
    /**
     * Generate the guest gender entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('gender')
            ->label(__('Gender'))
            ->badge();
    }
}
