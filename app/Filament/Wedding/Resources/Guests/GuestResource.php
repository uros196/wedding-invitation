<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Guests;

use App\Concerns\RelationScopedResource;
use App\Filament\Wedding\Resources\Guests\Pages\CreateGuest;
use App\Filament\Wedding\Resources\Guests\Pages\EditGuest;
use App\Filament\Wedding\Resources\Guests\Pages\ListGuests;
use App\Filament\Wedding\Resources\Guests\Schemas\GuestForm;
use App\Filament\Wedding\Resources\Guests\Tables\GuestsTable;
use App\Models\Guest;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Resource for managing individual guests.
 */
class GuestResource extends Resource
{
    use RelationScopedResource;

    /**
     * Representing model.
     *
     * @var null|class-string<Guest>
     */
    protected static ?string $model = Guest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    /**
     * Get the name of the relationship to scope the resource by.
     */
    protected static function getScopeRelation(): string
    {
        return 'team';
    }

    /**
     * Get the key of the related model to scope by.
     */
    protected static function getRelatedKey(): string|int|null
    {
        return auth()->user()?->team_id;
    }

    /**
     * Get a translatable model name.
     */
    public static function getModelLabel(): string
    {
        return __('Guest');
    }

    /**
     * Get a translatable plural model name.
     */
    public static function getPluralModelLabel(): string
    {
        return __('Guests');
    }

    /**
     * Configure the form schema.
     */
    public static function form(Schema $schema): Schema
    {
        return GuestForm::configure($schema);
    }

    /**
     * Configure the table.
     */
    public static function table(Table $table): Table
    {
        return GuestsTable::configure($table);
    }

    /**
     * Get the relationship managers for the resource.
     */
    public static function getRelations(): array
    {
        return [
            //
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
            'index' => ListGuests::route('/'),
            'create' => CreateGuest::route('/create'),
            'edit' => EditGuest::route('/{record}/edit'),
        ];
    }
}
