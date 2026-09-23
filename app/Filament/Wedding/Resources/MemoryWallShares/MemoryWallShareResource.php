<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallShares;

use App\Concerns\RelationScopedResource;
use App\Enums\NavigationGroup;
use App\Filament\Wedding\Resources\MemoryWallShares\Pages\CreateMemoryWallShare;
use App\Filament\Wedding\Resources\MemoryWallShares\Pages\EditMemoryWallShare;
use App\Filament\Wedding\Resources\MemoryWallShares\Pages\ListMemoryWallShares;
use App\Filament\Wedding\Resources\MemoryWallShares\Schemas\MemoryWallShareForm;
use App\Filament\Wedding\Resources\MemoryWallShares\Tables\MemoryWallSharesTable;
use App\Models\MemoryWallShare;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemoryWallShareResource extends Resource
{
    use RelationScopedResource;

    protected static ?string $model = MemoryWallShare::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * Determine whether share-link management is available for the current wedding.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', self::$model) === true;
    }

    /**
     * Get the navigation group.
     */
    public static function getNavigationGroup(): \UnitEnum
    {
        return NavigationGroup::Wedding;
    }

    /**
     * Get the relationship used to scope share links.
     */
    protected static function getScopeRelation(): string
    {
        return 'wedding';
    }

    /**
     * Resolve the current wedding from the authenticated team.
     */
    protected static function getRelatedKey(): string|int|null
    {
        return auth()->user()?->team?->wedding?->getKey();
    }

    /**
     * Get the singular resource label.
     */
    public static function getModelLabel(): string
    {
        return __('Memory Wall Share');
    }

    /**
     * Get the plural resource label.
     */
    public static function getPluralModelLabel(): string
    {
        return __('Memory Wall Shares');
    }

    public static function form(Schema $schema): Schema
    {
        return MemoryWallShareForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemoryWallSharesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemoryWallShares::route('/'),
            'create' => CreateMemoryWallShare::route('/create'),
            'edit' => EditMemoryWallShare::route('/{record}/edit'),
        ];
    }
}
