<?php

namespace App\Filament\Wedding\Resources\Groups\Tables\Columns;

use Filament\Tables\Columns\ToggleColumn;

class IsSentColumn
{
    /**
     * Generate a 'Sent' column.
     */
    public static function make(): ToggleColumn
    {
        return ToggleColumn::make('is_sent')
            ->label('Poslato')
            ->sortable();
    }
}
