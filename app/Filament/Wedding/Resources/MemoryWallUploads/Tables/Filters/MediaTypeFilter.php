<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Filters;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Generate the memory wall upload media type filter.
 */
class MediaTypeFilter
{
    /**
     * Generate the media type filter.
     */
    public static function make(): SelectFilter
    {
        return SelectFilter::make('media_type')
            ->label(__('wedding.memory_wall.filters.media_type'))
            ->placeholder(__('wedding.memory_wall.filters.all_media'))
            ->options([
                'image' => __('wedding.memory_wall.filters.images'),
                'video' => __('wedding.memory_wall.filters.videos'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return match ($data['value'] ?? null) {
                    'image' => $query->where('mime_type', 'like', 'image/%'),
                    'video' => $query->where('mime_type', 'like', 'video/%'),
                    default => $query,
                };
            });
    }
}
