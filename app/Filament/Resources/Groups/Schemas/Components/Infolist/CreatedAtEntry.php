<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class CreatedAtEntry
{
    /**
     * Generate the creation date entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('created_at')
            ->label(__('Created At'))
            ->dateTime();
    }
}
