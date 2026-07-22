<?php

namespace App\Filament\Wedding\Resources\Guests\Schemas\Components;

use App\Enums\Gender;
use Filament\Forms\Components\Select;

class GenderSelect
{
    /**
     * Generates a gender select input.
     */
    public static function make(): Select
    {
        return Select::make('gender')
            ->label(__('Gender'))
            ->options(Gender::class);
    }
}
