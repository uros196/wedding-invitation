<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Users\Schemas;

use App\Enums\UserType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * Defines the user form fields.
 */
class UserForm
{
    /**
     * Configure the user form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('user_type')
                    ->options([
                        UserType::ManagementAdmin->value => 'Management administrator',
                        UserType::WeddingUser->value => 'Wedding user',
                    ])
                    ->default(UserType::WeddingUser->value)
                    ->required()
                    ->live(),

                Select::make('team_id')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->visible(fn (Get $get): bool => $get('user_type') === UserType::WeddingUser->value)
                    ->required(fn (Get $get): bool => $get('user_type') === UserType::WeddingUser->value)
                    ->nullable(),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8),
            ]);
    }
}
