<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class InvitationTitleEntry
{
    /**
     * Generate the invitation title entry.
     */
    public static function make(): TextEntry
    {
        return TextEntry::make('invitation_title')
            ->label(__('Invitation Title'))
            ->placeholder(__('Not set'))
            ->columnSpanFull();
    }
}
