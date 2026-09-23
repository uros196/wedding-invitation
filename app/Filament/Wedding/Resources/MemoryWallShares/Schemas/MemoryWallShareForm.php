<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallShares\Schemas;

use App\Models\MemoryWallShare;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemoryWallShareForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components());
    }

    /**
     * Define the fields shared by the resource form and quick-create action.
     *
     * @return array<int, mixed>
     */
    public static function components(): array
    {
        return [
            Hidden::make('wedding_id')
                ->dehydrated(false),
            Section::make(__('Memory Wall Share'))
                ->description(__('wedding.memory_wall.share.form.description'))
                ->schema([
                    Section::make(__('Advanced Options'))
                        ->collapsed()
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->label(__('Name'))
                                        ->nullable()
                                        ->string()
                                        ->maxLength(255)
                                        ->helperText(__('wedding.memory_wall.share.form.name_help')),
                                    TextInput::make('password')
                                        ->label(__('Password'))
                                        ->password()
                                        ->revealable()
                                        ->placeholder(__('wedding.memory_wall.share.form.password_placeholder'))
                                        ->helperText(__('wedding.memory_wall.share.form.password_help'))
                                        ->afterStateHydrated(
                                            fn (TextInput $component): TextInput => $component->state(null),
                                        )
                                        ->dehydrated(fn (?string $state): bool => filled($state)),
                                    Toggle::make('clear_password')
                                        ->label(__('Remove password protection'))
                                        ->helperText(__('wedding.memory_wall.share.form.clear_password_help'))
                                        ->default(false)
                                        ->dehydrated(false)
                                        ->visible(fn (?MemoryWallShare $record): bool => filled($record?->password)),
                                    DateTimePicker::make('expires_at')
                                        ->label(__('Expires At'))
                                        ->placeholder(__('wedding.memory_wall.share.form.expiry_placeholder'))
                                        ->helperText(__('wedding.memory_wall.share.form.expiry_help'))
                                        ->rule('after:now'),
                                    Toggle::make('allow_downloads')
                                        ->label(__('Allow Downloads'))
                                        ->helperText(__('wedding.memory_wall.share.form.allow_downloads_help'))
                                        ->default(false),
                                ]),
                        ]),
                ]),
        ];
    }
}
