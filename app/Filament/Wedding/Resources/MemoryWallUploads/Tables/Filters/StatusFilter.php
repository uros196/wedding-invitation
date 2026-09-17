<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Filters;

use App\Enums\MemoryWallUploadStatus;
use Filament\Tables\Filters\SelectFilter;

/**
 * Generate the memory wall upload status filter.
 */
class StatusFilter
{
    /**
     * Generate the status filter.
     */
    public static function make(): SelectFilter
    {
        return SelectFilter::make('status')
            ->label(__('Status'))
            ->options(MemoryWallUploadStatus::class);
    }
}
