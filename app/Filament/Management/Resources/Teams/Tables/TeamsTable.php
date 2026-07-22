<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Teams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * Defines the team table columns and actions.
 */
class TeamsTable
{
    /**
     * Configure the team table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('wedding.bride_name')
                    ->label(__('Bride'))
                    ->searchable(),
                TextColumn::make('wedding.groom_name')
                    ->label(__('Groom'))
                    ->searchable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('Users'))
                    ->sortable(),
                ToggleColumn::make('has_memory_wall')
                    ->label(__('Memory Wall')),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
