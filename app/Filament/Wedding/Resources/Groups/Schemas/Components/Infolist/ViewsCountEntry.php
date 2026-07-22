<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;

class ViewsCountEntry
{
    /**
     * Generate the invitation views count entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('views_count')
            ->label(__('Views Count'))
            ->numeric()
            ->icon(Heroicon::OutlinedEye);
    }
}
