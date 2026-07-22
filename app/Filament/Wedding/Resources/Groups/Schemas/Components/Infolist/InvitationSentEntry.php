<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\IconEntry;

class InvitationSentEntry
{
    /**
     * Generate the invitation sent entry.
     */
    public static function make(): IconEntry
    {
        return IconEntry::make('is_sent')
            ->label(__('messages.invitation_sent'))
            ->boolean();
    }
}
