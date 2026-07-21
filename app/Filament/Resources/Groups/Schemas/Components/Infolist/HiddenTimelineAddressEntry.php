<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class HiddenTimelineAddressEntry
{
    /**
     * Generate the hidden timeline address entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('address')
            ->label(__('Address'))
            ->placeholder(__('Not set'));
    }
}
