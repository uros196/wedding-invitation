<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Users\Tables;

use App\Enums\UserType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Defines the user table columns and actions.
 */
class UsersTable
{
    /**
     * Configure the user table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user_type')
                    ->label('Type')
                    ->formatStateUsing(fn (UserType $state): string => match ($state) {
                        UserType::ManagementAdmin => 'Management administrator',
                        UserType::WeddingUser => 'Wedding user',
                    })
                    ->badge(),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->placeholder('—')
                    ->searchable(),
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
