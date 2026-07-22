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
            ->label(__('messages.hidden_timeline_items'))
            ->schema([
                TextEntry::make('list_name')
                    ->label(__('messages.timeline_item')),
                HiddenTimelineAddressEntry::make(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }
}
