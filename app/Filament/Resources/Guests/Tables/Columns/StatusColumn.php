<?php

namespace App\Filament\Resources\Guests\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class StatusColumn
{
    /**
     * Generates a status column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('status')
            ->label(__('Attendance Status'))
            ->badge()
            ->sortable();
    }
}
