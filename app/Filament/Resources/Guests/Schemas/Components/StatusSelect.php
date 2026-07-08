<?php

namespace App\Filament\Resources\Guests\Schemas\Components;

use App\Enums\GuestStatus;
use Filament\Forms\Components\Select;

class StatusSelect
{
    /**
     * Generates a status select input.
     */
    public static function make(): Select
    {
        return Select::make('status')
            ->label('Status prisustva')
            ->options(GuestStatus::class)
            ->default(GuestStatus::Pending)
            ->required()
            ->string();
    }
}
