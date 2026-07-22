<?php

namespace App\Filament\Wedding\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\TextInput;

class MetaTitleInput
{
    /**
     * Generate meta title input.
     */
    public static function make(): TextInput
    {
        return TextInput::make('meta_title')
            ->label(__('Meta Title'))
            ->placeholder(__(config('wedding.meta.title')))
            ->maxLength(255);
    }
}
