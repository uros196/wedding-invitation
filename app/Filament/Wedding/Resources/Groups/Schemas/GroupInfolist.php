<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas;

use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\ConfirmedGuestsEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\CreatedAtEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\DeclinedGuestsEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\GuestsCountEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\GuestsEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\HiddenTimelineItemsEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\InvitationMessageEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\InvitationSentEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\InvitationTitleEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\InvitationUrlEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\MessagesCountEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\MetaDescriptionEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\MetaImageEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\MetaTitleEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\NameEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\PendingGuestsEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\PlusOneAllowedEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\TimelineVisibilityEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\UpdatedAtEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\UuidEntry;
use App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist\ViewsCountEntry;
use App\Models\Group;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Infolist schema for displaying a guest group.
 */
class GroupInfolist
{
    /**
     * Configure the infolist schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make(__('messages.basic_info'))
                                    ->description(__('messages.group.basic_info_description'))
                                    ->columns(2)
                                    ->schema([
                                        NameEntry::make(),
                                        UuidEntry::make(),
                                        InvitationTitleEntry::make(),
                                        InvitationMessageEntry::make(),
                                    ]),

                                Section::make(__('messages.meta.data'))
                                    ->description(__('messages.group.meta_description'))
                                    ->columns(2)
                                    ->collapsible()
                                    ->collapsed(fn (Group $record): bool => ! $record->hasAnyMeta())
                                    ->schema([
                                        MetaTitleEntry::make(),
                                        MetaImageEntry::make(),
                                        MetaDescriptionEntry::make(),
                                    ]),
                            ]),
                        Grid::make(1)
                            ->schema([
                                Section::make(__('messages.invitation_status'))
                                    ->columns(2)
                                    ->schema([
                                        InvitationSentEntry::make(),
                                        PlusOneAllowedEntry::make(),
                                        ViewsCountEntry::make(),
                                        GuestsCountEntry::make(),
                                        MessagesCountEntry::make(),
                                        CreatedAtEntry::make(),
                                        UpdatedAtEntry::make(),
                                        InvitationUrlEntry::make(),
                                    ]),

                                Section::make(__('Timeline'))
                                    ->description(__('messages.timeline_description'))
                                    ->schema([
                                        TimelineVisibilityEntry::make(),
                                        HiddenTimelineItemsEntry::make(),
                                    ]),
                            ]),
                    ]),

                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('messages.guest_summary'))
                            ->schema([
                                ConfirmedGuestsEntry::make(),
                                PendingGuestsEntry::make(),
                                DeclinedGuestsEntry::make(),
                                GuestsEntry::make(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
