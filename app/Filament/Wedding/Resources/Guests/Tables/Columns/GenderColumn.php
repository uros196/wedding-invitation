<?php

namespace App\Filament\Wedding\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class GenderColumn
{
    /**
     * Generates a gender column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('gender')
            ->label(__('Gender'))
            ->placeholder('-')
            ->badge()
            ->sortable();
    }
}
