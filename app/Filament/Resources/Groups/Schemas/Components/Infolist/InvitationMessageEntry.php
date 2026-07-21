<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class InvitationMessageEntry
{
    /**
     * Generate the invitation message entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('invitation_message')
            ->label(__('messages.invitation_message'))
            ->placeholder(__('Not set'))
            ->columnSpanFull();
    }
}
