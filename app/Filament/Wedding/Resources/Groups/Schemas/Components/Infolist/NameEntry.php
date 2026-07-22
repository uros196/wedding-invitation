<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;

class NameEntry
{
    /**
     * Generate the group name entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('name')
            ->label(__('Name'))
            ->icon(Heroicon::OutlinedUserGroup);
    }
}
