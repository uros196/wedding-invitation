<?php

namespace App\Filament\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class NotesColumn
{
    /**
     * Generate a 'Notes' column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('notes')
            ->label('Napomene')
            ->limit(30)
            ->toggleable();
    }
}
