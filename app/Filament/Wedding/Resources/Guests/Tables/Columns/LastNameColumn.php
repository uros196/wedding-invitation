<?php

namespace App\Filament\Wedding\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class LastNameColumn
{
    /**
     * Generates a last name column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('last_name')
            ->label(__('Last Name'))
            ->searchable()
            ->sortable();
    }
}
