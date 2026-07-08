<?php

namespace App\Filament\Resources\Groups\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class GuestsCountColumn
{
    /**
     * Generate a 'Guests count' column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('guests_count')
            ->label('Broj gostiju')
            ->counts('guests')
            ->sortable();
    }
}
