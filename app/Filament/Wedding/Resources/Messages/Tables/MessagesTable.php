<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Messages\Tables;

use App\Filament\Columns\CreatedAtColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessagesTable
{
    /**
     * Configure the given table with columns, filters, sorting, record actions, and toolbar actions.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content')
                    ->limit(50)
                    ->searchable(),
                CreatedAtColumn::make(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
