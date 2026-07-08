<?php

namespace App\Filament\Resources\Groups\Schemas\Components;

use Filament\Forms\Components\TextInput;

class NameInput
{
    /**
     * Generate a name input field.
     */
    public static function make(): TextInput
    {
        return TextInput::make('name')
            ->label('Naziv grupe')
            ->placeholder('npr. Porodica Petrović')
            ->required()
            ->string()
            ->maxLength(255);
    }
}
