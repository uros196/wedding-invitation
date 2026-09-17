<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Columns;

use App\Models\MemoryWallUpload;
use App\Support\MemoryWall\MemoryWallMediaType;
use Filament\Tables\Columns\ImageColumn;

class MediaPreviewColumn
{
    /**
     * Generate the media preview column.
     */
    public static function make(): ImageColumn
    {
        return ImageColumn::make('media_preview')
            ->label(__('Preview'))
            ->state(fn (MemoryWallUpload $record): ?string => MemoryWallMediaType::isVideo($record->mime_type)
                ? MemoryWallMediaType::videoPlaceholderUrl()
                : MemoryWallMediaType::previewUrl($record->media))
            ->imageSize(72)
            ->square()
            ->checkFileExistence(false);
    }
}
