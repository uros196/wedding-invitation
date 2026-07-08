<?php

namespace App\Filament\Resources\Groups\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class ViewsCountColumn
{
    /**
     * Generate a 'Views count' column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('views_count')
            ->label('Pregleda')
            ->numeric()
            ->sortable();
    }
}
