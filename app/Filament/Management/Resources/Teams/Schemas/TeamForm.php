<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Teams\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput;

/**
 * Defines the team form fields.
 */
class TeamForm
{
    /**
     * Configure the team form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->live()
                    ->showCharacterCounter(fn (Get $get) => filled($get('name')))
                    ->maxLength(60),

                Toggle::make('has_memory_wall')
                    ->label(__('Memory Wall'))
                    ->default(false),
            ]);
    }
}
