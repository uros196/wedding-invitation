<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

class HiddenTimelineItemsEntry
{
    /**
     * Generate the hidden timeline items entry.
     */
    public static function make(): RepeatableEntry
    {
        return RepeatableEntry::make('hiddenTimelineItems')
            ->label(__('wedding.groups.timeline.hidden_items'))
            ->schema([
                TextEntry::make('list_name')
                    ->label(__('Timeline Item')),
                HiddenTimelineAddressEntry::make(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }
}
