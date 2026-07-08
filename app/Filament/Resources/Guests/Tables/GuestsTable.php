<?php

namespace App\Filament\Resources\Guests\Tables;

use App\Enums\Age;
use App\Enums\Gender;
use App\Enums\GuestStatus;
use App\Filament\Columns\CreatedAtColumn;
use App\Filament\Resources\Guests\Tables\Columns\AgeColumn;
use App\Filament\Resources\Guests\Tables\Columns\FirstNameColumn;
use App\Filament\Resources\Guests\Tables\Columns\GenderColumn;
use App\Filament\Resources\Guests\Tables\Columns\GuestGroupColumn;
use App\Filament\Resources\Guests\Tables\Columns\LastNameColumn;
use App\Filament\Resources\Guests\Tables\Columns\ParentColumn;
use App\Filament\Resources\Guests\Tables\Columns\StatusColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Table configuration for the Guest resource.
 */
class GuestsTable
{
    /**
     * Configure the table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                FirstNameColumn::make(),
                LastNameColumn::make(),
                AgeColumn::make(),
                GenderColumn::make(),
                GuestGroupColumn::make(),
                ParentColumn::make(),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status prisustva')
                    ->options(GuestStatus::class),

                SelectFilter::make('group')
                    ->label('Grupa')
                    ->relationship('group', 'name'),

                SelectFilter::make('age')
                    ->label('Uzrast')
                    ->options(Age::class),

                SelectFilter::make('gender')
                    ->label('Pol')
                    ->options(Gender::class),
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
