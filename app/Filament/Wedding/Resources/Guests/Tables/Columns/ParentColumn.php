<?php

namespace App\Filament\Wedding\Resources\Guests\Tables\Columns;

use App\Models\Guest;
use Filament\Tables\Columns\TextColumn;

class ParentColumn
{
    /**
     * Generate 'Parent' column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('parent.full_name')
            ->label('Kompanjon od')
            ->state(fn (Guest $record) => $record->parent?->full_name)
            ->placeholder('-')
            ->toggleable();
    }
}
