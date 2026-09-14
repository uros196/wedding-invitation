<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Schemas;

use App\Models\MemoryWallUpload;
use App\Support\MemoryWall\MemoryWallMediaType;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

/**
 * Defines the read-only details shown for one memory wall upload.
 */
class MemoryWallUploadInfolist
{
    /**
     * Configure upload metadata, final status, and the stored file link.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 3,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('Preview'))
                            ->schema([
                                ImageEntry::make('media_preview')
                                    ->hiddenLabel()
                                    ->state(fn (MemoryWallUpload $record): ?string => MemoryWallMediaType::preview($record->media))
                                    ->imageWidth(400)
                                    ->imageHeight('auto')
                                    ->checkFileExistence(false)
                                    ->visible(fn (MemoryWallUpload $record): bool => ! static::isVideo($record)),

                                ViewEntry::make('video_preview')
                                    ->hiddenLabel()
                                    ->view('filament.wedding.resources.memory-wall-uploads.infolist.video-preview')
                                    ->viewData(fn (MemoryWallUpload $record): array => [
                                        'placeholderUrl' => MemoryWallMediaType::videoPlaceholderUrl(),
                                        'videoUrl' => $record->media?->getUrl(),
                                        'mimeType' => $record->mime_type,
                                    ])
                                    ->visible(fn (MemoryWallUpload $record): bool => static::isVideo($record)),
                            ])
                            ->columnSpan(['lg' => 2])
                            ->visible(fn (MemoryWallUpload $record): bool => $record->media !== null),

                        Section::make(__('File details'))
                            ->schema([
                                TextEntry::make('original_name')
                                    ->label(__('File')),
                                TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge(),
                                TextEntry::make('mime_type')
                                    ->label(__('Type')),
                                TextEntry::make('expected_size')
                                    ->label(__('Size'))
                                    ->formatStateUsing(fn (int $state): string => Number::fileSize($state)),
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime(),
                                TextEntry::make('completed_at')
                                    ->label(__('Completed At'))
                                    ->dateTime(),
                                TextEntry::make('media.original_url')
                                    ->label(__('Open file'))
                                    ->url(fn (MemoryWallUpload $record): ?string => $record->media?->getUrl())
                                    ->openUrlInNewTab()
                                    ->copyable()
                                    ->visible(fn (MemoryWallUpload $record): bool => $record->media !== null),
                                TextEntry::make('error_message')
                                    ->label(__('Error'))
                                    ->color('danger')
                                    ->visible(fn (MemoryWallUpload $record): bool => filled($record->error_message)),
                            ])
                            ->columnSpan(fn (MemoryWallUpload $record): array => $record->media !== null
                                ? ['lg' => 1]
                                : ['lg' => 3])
                            ->columns(1),
                    ]),
            ]);
    }

    /**
     * Determines if the given MemoryWallUpload record is of video type
     * based on its MIME type.
     */
    protected static function isVideo(?MemoryWallUpload $record): bool
    {
        return MemoryWallMediaType::isVideo($record?->mime_type);
    }
}
