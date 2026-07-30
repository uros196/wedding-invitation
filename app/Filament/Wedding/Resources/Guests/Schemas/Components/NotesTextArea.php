<?php

namespace App\Filament\Wedding\Resources\Guests\Schemas\Components;

use Filament\Forms\Components\Textarea;

class NotesTextArea
{
    /**
     * Generates a note textarea input.
     */
    public static function make(): Textarea
    {
        return Textarea::make('notes')
            ->label(__('Notes'))
            ->placeholder(__('wedding.guests.form.notes_placeholder'))
            ->rows(3)
            ->string()
            ->nullable()
            ->columnSpanFull();
    }
}
