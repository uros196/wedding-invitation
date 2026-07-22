<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class MetaTitleEntry
{
    /**
     * Generate the metadata title entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('meta_title')
            ->label(__('messages.meta.title'))
            ->placeholder(__('Not set'));
    }
}
