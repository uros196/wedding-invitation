<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class MetaDescriptionEntry
{
    /**
     * Generate the metadata description entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('meta_description')
            ->label(__('messages.meta.description'))
            ->placeholder(__('Not set'))
            ->columnSpanFull();
    }
}
