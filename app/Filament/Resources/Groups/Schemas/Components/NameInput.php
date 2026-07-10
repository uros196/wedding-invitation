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
            ->label(__('Group Name'))
            ->placeholder(__('e.g. Petrović Family'))
            ->required()
            ->string()
            ->maxLength(255);
    }
}
