<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads;

use App\Concerns\RelationScopedResource;
use App\Enums\NavigationGroup;
use App\Filament\Wedding\Resources\MemoryWallUploads\Pages\ListMemoryWallUploads;
use App\Filament\Wedding\Resources\MemoryWallUploads\Pages\ViewMemoryWallUpload;
use App\Filament\Wedding\Resources\MemoryWallUploads\Schemas\MemoryWallUploadInfolist;
use App\Filament\Wedding\Resources\MemoryWallUploads\Tables\MemoryWallUploadsTable;
use App\Models\MemoryWallUpload;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Filament resource for reviewing uploads belonging to the current wedding.
 *
 * The resource is read-oriented: files are created by the public upload flow,
 * while wedding users can inspect or remove them from the panel.
 */
class MemoryWallUploadResource extends Resource
{
    use RelationScopedResource;

    protected static ?string $model = MemoryWallUpload::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): \UnitEnum
    {
        return NavigationGroup::Wedding;
    }

    /**
     * Tell RelationScopedResource which model relationship owns each upload.
     */
    protected static function getScopeRelation(): string
    {
        return 'wedding';
    }

    /**
     * Resolve the wedding key from the currently authenticated team.
     */
    protected static function getRelatedKey(): string|int|null
    {
        return auth()->user()?->team?->wedding?->getKey();
    }

    public static function getModelLabel(): string
    {
        return __('Memory upload');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Memory uploads');
    }

    /**
     * Configure the read-only upload details shown on the view page.
     */
    public static function infolist(Schema $schema): Schema
    {
        return MemoryWallUploadInfolist::configure($schema);
    }

    /**
     * Configure the upload list with status, size, and destructive actions.
     */
    public static function table(Table $table): Table
    {
        return MemoryWallUploadsTable::configure($table);
    }

    /**
     * Uploads have no nested relation managers in the wedding panel.
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Expose listing and detail pages only; creation and editing happen via the
     * public multipart upload workflow.
     */
    public static function getPages(): array
    {
        return [
            'index' => ListMemoryWallUploads::route('/'),
            'view' => ViewMemoryWallUpload::route('/{record}'),
        ];
    }
}
