<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\RelationManagers;

use App\Filament\Wedding\Resources\Guests\Schemas\Grids\ModalGrid;
use App\Filament\Wedding\Resources\Guests\Tables\Columns\AgeColumn;
use App\Filament\Wedding\Resources\Guests\Tables\Columns\FirstNameColumn;
use App\Filament\Wedding\Resources\Guests\Tables\Columns\GenderColumn;
use App\Filament\Wedding\Resources\Guests\Tables\Columns\LastNameColumn;
use App\Filament\Wedding\Resources\Guests\Tables\Columns\NotesColumn;
use App\Filament\Wedding\Resources\Guests\Tables\Columns\ParentColumn;
use App\Filament\Wedding\Resources\Guests\Tables\Columns\StatusColumn;
use App\Traits\TableRefreshable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Relation manager for guests within a group.
 */
class GuestsRelationManager extends RelationManager
{
    use TableRefreshable;

    protected static string $relationship = 'guests';

    /**
     * Configure the form schema for the relation manager.
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components(ModalGrid::make());
    }

    /**
     * Configure the table for the relation manager.
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                FirstNameColumn::make(),
                LastNameColumn::make(),
                AgeColumn::make(),
                GenderColumn::make(),
                ParentColumn::make(),
                StatusColumn::make(),
                NotesColumn::make(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::Plus)
                    ->label(__('Add Guest')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Refresh the table when this broadcast event occurs.
     */
    protected function refreshTableOn(): string|array
    {
        return '.attendanceConfirmed';
    }
}
