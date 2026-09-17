<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Columns;

use App\Models\MemoryWallUpload;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Number;

class OriginalNameColumn
{
    /**
     * Generate the original file name column.
     */
    public static function make(): TextColumn
    {
        return TextColumn::make('original_name')
            ->label(__('File'))
            ->searchable()
            ->sortable()
            ->weight(FontWeight::Medium)
            ->description(fn (MemoryWallUpload $record): string => sprintf(
                '%s, %s',
                $record->mime_type,
                Number::fileSize($record->expected_size),
            ))
            ->wrap();
    }
}
