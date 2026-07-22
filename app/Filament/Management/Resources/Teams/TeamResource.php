<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Teams;

use App\Filament\Management\Resources\Teams\Pages\CreateTeam;
use App\Filament\Management\Resources\Teams\Pages\EditTeam;
use App\Filament\Management\Resources\Teams\Pages\ListTeams;
use App\Filament\Management\Resources\Teams\RelationManagers\UsersRelationManager;
use App\Filament\Management\Resources\Teams\Schemas\TeamForm;
use App\Filament\Management\Resources\Teams\Tables\TeamsTable;
use App\Models\Team;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Provides team management resources for the management panel.
 */
class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TeamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeams::route('/'),
            'create' => CreateTeam::route('/create'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }
}
