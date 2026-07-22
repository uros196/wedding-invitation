<?php

namespace App\Filament\Wedding\Resources\Guests\Schemas\Components;

use Filament\Forms\Components\TextInput;

class FirstNameInput
{
    /**
     * Generates a first name input.
     */
    public static function make(): TextInput
    {
        return TextInput::make('first_name')
            ->label(__('First Name'))
            ->required()
            ->string()
            ->maxLength(255);
    }
}
