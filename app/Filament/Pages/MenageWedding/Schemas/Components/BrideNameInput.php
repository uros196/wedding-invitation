<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\TextInput;

class BrideNameInput
{
    /**
     * Generate the input for the bride's name.
     */
    public static function make(): TextInput
    {
        return TextInput::make('bride_name')
            ->label(__('Bride\'s Name'))
            ->required();
    }
}
