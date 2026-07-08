<?php

namespace App\Filament\Resources\Guests\Schemas\Components;

use Filament\Forms\Components\Textarea;

class NotesTextArea
{
    /**
     * Generates a note textarea input.
     */
    public static function make(): Textarea
    {
        return Textarea::make('notes')
            ->label('Napomene')
            ->placeholder('npr. Alergije, posebne želje...')
            ->rows(3)
            ->string()
            ->nullable()
            ->columnSpanFull();
    }
}
