<?php

namespace App\Filament\Wedding\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class NotesColumn
{
    /**
     * Generate a 'Notes' column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('notes')
            ->label(__('Notes'))
            ->limit(30)
            ->toggleable();
    }
}
