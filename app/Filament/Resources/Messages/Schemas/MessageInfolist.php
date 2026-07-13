<?php

declare(strict_types=1);

namespace App\Filament\Resources\Messages\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MessageInfolist
{
    /**
     * Configure the schema components for the section.
     *
     * This method defines and returns the schema for a section, including its components
     * and layout structure. The schema includes a grouping section with fields for group
     * details and a nested repeatable section for guests, allowing dynamic data entry for
     * each guest's information.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('group.name')
                            ->label(__('Group')),
                        TextEntry::make('created_at')
                            ->label(__('Created At'))
                            ->dateTime(),
                        TextEntry::make('content')
                            ->label(__('Message'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make()
                    ->schema([
                        RepeatableEntry::make('group.guests')
                            ->label(__('Guests'))
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label(__('Full name')),
                                TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
