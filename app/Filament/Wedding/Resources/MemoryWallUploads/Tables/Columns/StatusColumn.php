<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class StatusColumn
{
    /**
     * Generate the upload status column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('status')
            ->label(__('Status'))
            ->badge()
            ->sortable();
    }
}
