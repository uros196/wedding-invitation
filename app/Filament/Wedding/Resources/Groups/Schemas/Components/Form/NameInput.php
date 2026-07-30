<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

use Filament\Schemas\Components\Utilities\Get;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput;

class NameInput
{
    /**
     * Generate a name input field.
     */
    public static function make(): TextInput
    {
        return TextInput::make('name')
            ->label(__('Group Name'))
            ->placeholder(__('wedding.groups.form.name_placeholder'))
            ->required()
            ->string()
            ->live()
            ->showCharacterCounter(fn (Get $get) => filled($get('name')))
            ->maxLength(100);
    }
}
