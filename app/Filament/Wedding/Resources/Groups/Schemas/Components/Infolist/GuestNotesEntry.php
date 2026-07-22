<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class GuestNotesEntry
{
    /**
     * Generate the guest notes entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('notes')
            ->label(__('Notes'))
            ->placeholder(__('Not set'))
            ->columnSpanFull();
    }
}
