<?php

namespace App\Filament\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class GuestGroupColumn
{
    /**
     * Generate a 'Guest group' column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('group.name')
            ->label('Grupa')
            ->searchable()
            ->sortable();
    }
}
