<?php

namespace App\Filament\Columns;

use Filament\Tables\Columns\TextColumn;

class CreatedAtColumn
{
    /**
     * Generate the 'Created at' column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('Created'))
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
