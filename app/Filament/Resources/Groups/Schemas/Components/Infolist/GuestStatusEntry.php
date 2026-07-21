<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class GuestStatusEntry
{
    /**
     * Generate the guest status entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('status')
            ->label(__('Status'))
            ->badge();
    }
}
