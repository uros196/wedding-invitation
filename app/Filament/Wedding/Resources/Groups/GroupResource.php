<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups;

use App\Concerns\RelationScopedResource;
use App\Enums\NavigationGroup;
use App\Filament\Wedding\Resources\Groups\Pages\CreateGroup;
use App\Filament\Wedding\Resources\Groups\Pages\EditGroup;
use App\Filament\Wedding\Resources\Groups\Pages\ListGroups;
use App\Filament\Wedding\Resources\Groups\Pages\ViewGroup;
use App\Filament\Wedding\Resources\Groups\RelationManagers\GuestsRelationManager;
use App\Filament\Wedding\Resources\Groups\Schemas\GroupForm;
use App\Filament\Wedding\Resources\Groups\Schemas\GroupInfolist;
use App\Filament\Wedding\Resources\Groups\Tables\GroupsTable;
use App\Models\Group;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Resource for managing guest groups.
 */
class GroupResource extends Resource
{
    use RelationScopedResource;

    protected static ?string $model = Group::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 1;

    /**
     * Determine whether group management is available.
     */
    public static function canAccess(): bool
    {
        return auth()->user()->can('access', self::$model);
    }

    /**
     * Get the navigation group.
     */
    public static function getNavigationGroup(): \UnitEnum
    {
        return NavigationGroup::Guests;
    }

    /**
     * Get the name of the relationship to scope the resource by.
     */
    protected static function getScopeRelation(): string
    {
        return 'wedding';
    }

    /**
     * Get the key of the related model to scope by.
     */
    protected static function getRelatedKey(): string|int|null
    {
        return auth()->user()?->team?->wedding?->id;
    }

    /**
     * Get a translatable model name.
     */
    public static function getModelLabel(): string
    {
        return __('Group');
    }

    /**
     * Get a translatable plural model name.
     */
    public static function getPluralModelLabel(): string
    {
        return __('Groups');
    }

    /**
     * Configure the form schema.
     */
    public static function form(Schema $schema): Schema
    {
        return GroupForm::configure($schema);
    }

    /**
     * Configure the infolist schema.
     */
    public static function infolist(Schema $schema): Schema
    {
        return GroupInfolist::configure($schema);
    }

    /**
     * Configure the table schema.
     */
    public static function table(Table $table): Table
    {
        return GroupsTable::configure($table);
    }

    /**
     * Get the relationship managers for the resource.
     */
    public static function getRelations(): array
    {
        return [
            GuestsRelationManager::class,
        ];
    }

    /**
     * Get the pages for the resource.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListGroups::route('/'),
            'create' => CreateGroup::route('/create'),
            'view' => ViewGroup::route('/{record}'),
            'edit' => EditGroup::route('/{record}/edit'),
        ];
    }
}
