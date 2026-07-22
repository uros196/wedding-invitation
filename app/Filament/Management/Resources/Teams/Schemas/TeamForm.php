<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Teams\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

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
                    ->maxLength(255),

                Toggle::make('has_memory_wall')
                    ->label(__('Memory Wall'))
                    ->default(false),
            ]);
    }
}
