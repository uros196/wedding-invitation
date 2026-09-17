<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables;

use App\Filament\Wedding\Resources\MemoryWallUploads\Actions\DownloadMemoryWallArchiveAction;
use App\Filament\Wedding\Resources\MemoryWallUploads\Actions\DownloadMemoryWallUploadAction;
use App\Filament\Wedding\Resources\MemoryWallUploads\Actions\DownloadSelectedMemoryWallArchiveAction;
use App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Columns\CreatedAtColumn;
use App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Columns\MediaPreviewColumn;
use App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Columns\OriginalNameColumn;
use App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Columns\StatusColumn;
use App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Filters\MediaTypeFilter;
use App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Filters\SizeFilter;
use App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Filters\StatusFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['media', 'wedding']))
            ->columns([
                MediaPreviewColumn::make(),
                OriginalNameColumn::make(),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->filters([
                StatusFilter::make(),
                MediaTypeFilter::make(),
                SizeFilter::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                DownloadMemoryWallUploadAction::make(),
                ViewAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                DownloadMemoryWallArchiveAction::make(),
                BulkActionGroup::make([
                    DownloadSelectedMemoryWallArchiveAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
