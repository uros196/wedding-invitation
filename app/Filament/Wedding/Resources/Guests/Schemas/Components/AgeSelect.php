<?php

namespace App\Filament\Wedding\Resources\Guests\Schemas\Components;

use App\Enums\Age;
use Filament\Forms\Components\Select;

class AgeSelect
{
    /**
     * Generates an age select input.
     */
    public static function make(): Select
    {
        return Select::make('age')
            ->label(__('Age'))
            ->options(Age::class)
            ->default(Age::Adult);
    }
}
