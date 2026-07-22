<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class UuidEntry
{
    /**
     * Generate the group UUID entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('uuid')
            ->label(__('UUID'))
            ->copyable();
    }
}
