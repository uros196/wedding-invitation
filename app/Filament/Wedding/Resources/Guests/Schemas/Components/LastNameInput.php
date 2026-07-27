<?php

namespace App\Filament\Wedding\Resources\Guests\Schemas\Components;

use Filament\Forms\Components\TextInput;

class LastNameInput
{
    /**
     * Generates a last name input.
     */
    public static function make(): TextInput
    {
        return TextInput::make('last_name')
            ->label(__('Last Name'))
            ->string()
            ->maxLength(255);
    }
}
