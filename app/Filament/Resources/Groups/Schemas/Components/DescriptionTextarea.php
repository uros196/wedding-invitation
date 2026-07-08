<?php

namespace App\Filament\Resources\Groups\Schemas\Components;

use Filament\Forms\Components\Textarea;

class DescriptionTextarea
{
    /**
     * Generate a description textarea input.
     */
    public static function make(): Textarea
    {
        return Textarea::make('description')
            ->label('Personalizovana poruka')
            ->placeholder('Ova poruka će biti prikazana na vrhu njihove pozivnice...')
            ->rows(3)
            ->string()
            ->nullable();
    }
}
