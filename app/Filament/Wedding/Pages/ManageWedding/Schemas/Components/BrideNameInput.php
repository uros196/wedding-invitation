<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use Filament\Schemas\Components\Utilities\Get;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput;

class BrideNameInput
{
    /**
     * Generate the input for the bride's name.
     */
    public static function make(): TextInput
    {
        return TextInput::make('bride_name')
            ->label(__('Bride\'s Name'))
            ->placeholder(__('wedding.manage_wedding.basic_information.bride_name_placeholder'))
            ->live()
            ->showCharacterCounter(fn (Get $get) => filled($get('bride_name')))
            ->maxLength(50)
            ->required();
    }
}
