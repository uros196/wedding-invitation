<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use Filament\Schemas\Components\Utilities\Get;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput;

class GroomNameInput
{
    /**
     * Generate the input for the groom's name.
     */
    public static function make(): TextInput
    {
        return TextInput::make('groom_name')
            ->label(__('Groom\'s Name'))
            ->live()
            ->showCharacterCounter(fn (Get $get) => filled($get('groom_name')))
            ->maxLength(50)
            ->required();
    }
}
