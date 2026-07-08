<?php

namespace App\Filament\Resources\Groups\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class GroupNameColumn
{
    /**
     * Generate a 'Name' column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('name')
            ->label('Naziv grupe')
            ->searchable()
            ->sortable();
    }
}
