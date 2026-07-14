<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas;

use App\Filament\Resources\Groups\Schemas\Components\DescriptionTextarea;
use App\Filament\Resources\Groups\Schemas\Components\MetaDescriptionTextarea;
use App\Filament\Resources\Groups\Schemas\Components\MetaImageFileUpload;
use App\Filament\Resources\Groups\Schemas\Components\MetaTitleInput;
use App\Filament\Resources\Groups\Schemas\Components\NameInput;
use App\Filament\Resources\Groups\Schemas\Components\UuidInput;
use App\Models\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Form schema configuration for the Group resource.
 */
class GroupForm
{
    /**
     * Configure the form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.basic_info'))
                    ->description(__('messages.group_basic_info_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                NameInput::make()->columnSpan(1),
                                UuidInput::make(),
                            ]),

                        DescriptionTextarea::make()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('messages.invitation_status'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_sent')
                                    ->label(__('Invitation Sent')),

                                TextInput::make('views_count')
                                    ->label(__('Views Count'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make(__('Meta Data'))
                    ->description(__('messages.group_meta_description'))
                    ->collapsible()
                    ->collapsed(fn (?Group $record): bool => !$record?->hasAnyMeta())
                    ->schema([
                        MetaTitleInput::make(),
                        MetaDescriptionTextarea::make(),
                        MetaImageFileUpload::make(),
                    ]),
            ]);
    }
}
