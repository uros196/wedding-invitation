<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Guests\Schemas;

use App\Filament\Wedding\Resources\Groups\GroupResource;
use App\Filament\Wedding\Resources\Guests\GuestResource;
use App\Models\Guest;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

/**
 * Infolist schema for displaying an individual guest.
 */
class GuestInfolist
{
    /**
     * Configure the infolist schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('Guest Information'))
                            ->icon(Heroicon::OutlinedUser)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label(__('Full name'))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->icon(Heroicon::OutlinedUser)
                                    ->columnSpanFull(),
                                TextEntry::make('first_name')
                                    ->label(__('First Name')),
                                TextEntry::make('last_name')
                                    ->label(__('Last Name'))
                                    ->placeholder(__('Not set')),
                                TextEntry::make('age')
                                    ->label(__('Age'))
                                    ->badge()
                                    ->placeholder(__('Not set')),
                                TextEntry::make('gender')
                                    ->label(__('Gender'))
                                    ->badge()
                                    ->placeholder(__('Not set')),
                            ])
                            ->columnSpan(2),

                        Section::make(__('Invitation Status'))
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->label(__('Attendance Status'))
                                    ->badge()
                                    ->columnSpanFull(),
                                TextEntry::make('group.name')
                                    ->label(__('Group'))
                                    ->placeholder(__('Not set'))
                                    ->url(fn (Guest $record): ?string => $record->group_id
                                        ? GroupResource::getUrl('view', ['record' => $record->group_id])
                                        : null),
                                TextEntry::make('parent.full_name')
                                    ->label(__('Companion for'))
                                    ->placeholder(__('Not set'))
                                    ->url(fn (Guest $record): ?string => $record->parent_id
                                        ? GuestResource::getUrl('view', ['record' => $record->parent_id])
                                        : null),
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label(__('Updated At'))
                                    ->dateTime(),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make(__('Companions'))
                    ->icon(Heroicon::OutlinedUsers)
                    ->description(__('wedding.guests.companions.description'))
                    ->schema([
                        RepeatableEntry::make('companions')
                            ->table([
                                TableColumn::make(__('Full name')),
                                TableColumn::make(__('Status')),
                                TableColumn::make(__('Age')),
                                TableColumn::make(__('Gender')),
                            ])
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label(__('Full name')),
                                TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge(),
                                TextEntry::make('age')
                                    ->label(__('Age'))
                                    ->badge()
                                    ->placeholder(__('Not set')),
                                TextEntry::make('gender')
                                    ->label(__('Gender'))
                                    ->badge()
                                    ->placeholder(__('Not set')),
                            ])
                            ->placeholder(__('wedding.guests.companions.empty'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('Notes'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextEntry::make('notes')
                            ->label(__('Notes'))
                            ->prose()
                            ->placeholder(__('Not set'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
