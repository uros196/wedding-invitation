<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use App\Models\Group;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;

class MessagesCountEntry
{
    /**
     * Generate the messages count entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('messages_count')
            ->label(__('Messages'))
            ->state(fn (Group $record): int => $record->messages()->count())
            ->numeric()
            ->icon(Heroicon::OutlinedChatBubbleLeftRight);
    }
}
