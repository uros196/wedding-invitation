<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class CreatedAtColumn
{
    /**
     * Generate the upload creation date column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('Created At'))
            ->dateTime()
            ->sortable();
    }
}
