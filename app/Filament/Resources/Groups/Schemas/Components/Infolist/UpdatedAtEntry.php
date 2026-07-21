<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class UpdatedAtEntry
{
    /**
     * Generate the last update date entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('updated_at')
            ->label(__('Updated At'))
            ->dateTime();
    }
}
