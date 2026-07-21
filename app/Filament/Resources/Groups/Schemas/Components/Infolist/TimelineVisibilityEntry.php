<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use App\Models\Group;
use Filament\Infolists\Components\TextEntry;

class TimelineVisibilityEntry
{
    /**
     * Generate the timeline visibility entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('timeline_visibility')
            ->label(__('Visibility'))
            ->state(fn (Group $record): string => $record->hiddenTimelineItems()->exists()
                ? __('Custom')
                : __('messages.all_timeline_items_visible'))
            ->badge()
            ->color(fn (Group $record): string => $record->hiddenTimelineItems()->exists()
                ? 'warning'
                : 'success');
    }
}
