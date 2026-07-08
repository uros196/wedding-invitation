<?php

namespace App\Filament\Resources\Guests\Schemas\Components;

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
            ->label('Pol')
            ->options(Gender::class);
    }
}
