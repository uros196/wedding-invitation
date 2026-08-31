<?php

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables;

use App\Enums\MemoryWallUploadStatus;
use App\Models\MemoryWallUpload;
use App\Support\MemoryWall\MemoryWallMediaType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

/**
 * Defines the table used to review and remove memory wall uploads.
 */
class MemoryWallUploadsTable
{
    /**
     * Configure columns, filters, and actions for the upload overview.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('media'))
            ->columns([
                ImageColumn::make('media_preview')
                    ->label(__('Preview'))
                    ->state(fn (MemoryWallUpload $record): ?string => MemoryWallMediaType::isVideo($record->mime_type)
                        ? MemoryWallMediaType::videoPlaceholderUrl()
                        : MemoryWallMediaType::previewUrl($record->media))
                    ->imageSize(64)
                    ->square()
                    ->checkFileExistence(false),
                TextColumn::make('original_name')
                    ->label(__('File'))
                    ->searchable()
                    ->sortable()
                    ->limit(32),
                TextColumn::make('mime_type')
                    ->label(__('Type'))
                    ->sortable(),
                TextColumn::make('expected_size')
                    ->label(__('Size'))
                    ->formatStateUsing(fn (int $state): string => Number::fileSize($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(MemoryWallUploadStatus::class),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
