<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use Filament\Infolists\Components\IconEntry;

class PlusOneAllowedEntry
{
    /**
     * Generate the plus-one permission entry.
     */
    public static function make(): IconEntry
    {
        return IconEntry::make('has_plus_one')
            ->label(__('messages.group.plus_one_allowed'))
            ->boolean();
    }
}
