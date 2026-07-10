<?php

namespace App\Filament\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class FirstNameColumn
{
    /**
     * Generates a first name column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('first_name')
            ->label(__('First Name'))
            ->searchable()
            ->sortable();
    }
}
