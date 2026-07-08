<?php

namespace App\Filament\Resources\Groups\Tables;

use App\Filament\Columns\CreatedAtColumn;
use App\Filament\Resources\Groups\Tables\Columns\GroupNameColumn;
use App\Filament\Resources\Groups\Tables\Columns\GuestsCountColumn;
use App\Filament\Resources\Groups\Tables\Columns\IsSentColumn;
use App\Filament\Resources\Groups\Tables\Columns\ViewsCountColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Table configuration for the Group resource.
 */
class GroupsTable
{
    /**
     * Configure the table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                GroupNameColumn::make(),
                GuestsCountColumn::make(),
                IsSentColumn::make(),
                ViewsCountColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->filters([
                TernaryFilter::make('is_sent')
                    ->label('Status slanja')
                    ->placeholder('Sve grupe')
                    ->trueLabel('Poslate pozivnice')
                    ->falseLabel('Neposlate pozivnice'),
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
}
