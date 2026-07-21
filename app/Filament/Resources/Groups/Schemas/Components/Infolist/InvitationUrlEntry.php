<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use App\Models\Group;
use Filament\Infolists\Components\TextEntry;

class InvitationUrlEntry
{
    /**
     * Generate the public invitation URL entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('url')
            ->label(__('messages.invitation_url'))
            ->state(fn (Group $record): string => route('group.show', $record))
            ->copyable()
            ->url(fn (Group $record): string => route('group.show', $record))
            ->openUrlInNewTab()
            ->columnSpanFull();
    }
}
