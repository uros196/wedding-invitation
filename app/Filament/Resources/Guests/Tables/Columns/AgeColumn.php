<?php

namespace App\Filament\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class AgeColumn
{
    /**
     * Generates an age column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('age')
            ->label(__('Age'))
            ->placeholder('-')
            ->badge()
            ->sortable();
    }
}
