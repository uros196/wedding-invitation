<?php

namespace App\Filament\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class GenderColumn
{
    /**
     * Generates a gender column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('gender')
            ->label('Pol')
            ->placeholder('-')
            ->badge()
            ->sortable();
    }
}
