<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\TextInput;

class GroomNameInput
{
    /**
     * Generate the input for the groom's name.
     */
    public static function make(): TextInput
    {
        return TextInput::make('groom_name')
            ->label(__('Groom\'s Name'))
            ->required();
    }
}
