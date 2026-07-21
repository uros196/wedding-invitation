<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas;

use App\Filament\Pages\MenageWedding\EmptyStates\NoTimelineDefinedState;
use App\Filament\Resources\Groups\Schemas\Components\Form\HasPlusOneToggle;
use App\Filament\Resources\Groups\Schemas\Components\Form\InvitationMessageTextarea;
use App\Filament\Resources\Groups\Schemas\Components\Form\InvitationTitleInput;
use App\Filament\Resources\Groups\Schemas\Components\Form\MetaDescriptionTextarea;
use App\Filament\Resources\Groups\Schemas\Components\Form\MetaImageFileUpload;
use App\Filament\Resources\Groups\Schemas\Components\Form\MetaTitleInput;
use App\Filament\Resources\Groups\Schemas\Components\Form\NameInput;
use App\Filament\Resources\Groups\Schemas\Components\Form\UuidInput;
use App\Filament\Resources\Groups\Schemas\Components\Form\WeddingTimelineList;
use App\Models\Group;
use App\Models\WeddingTimeline;
use Filament\Forms\Components\TextInput;
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
                Grid::make(1)
                    ->schema([
                        Section::make(__('messages.basic_info'))
                            ->description(__('messages.group.basic_info_description'))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        NameInput::make()->columnSpan(1),
                                        UuidInput::make(),
                                    ]),

                                InvitationTitleInput::make(),
                                InvitationMessageTextarea::make(),
                            ]),

                        Section::make(__('Meta Data'))
                            ->description(__('messages.group.meta_description'))
                            ->collapsible()
                            ->collapsed(fn (?Group $record): bool => ! $record?->hasAnyMeta())
                            ->schema([
                                MetaTitleInput::make(),
                                MetaDescriptionTextarea::make(),
                                MetaImageFileUpload::make(),
                            ]),
                    ]),

                Grid::make(1)
                    ->schema([
                        Section::make(__('messages.invitation_status'))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        HasPlusOneToggle::make(),

                                        TextInput::make('views_count')
                                            ->label(__('Views Count'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->disabled()
                                            ->dehydrated(false),
                                    ]),
                            ]),

                        Section::make(__('Timeline'))
                            ->description(__('Manage which timeline items are visible for this group.'))
                            ->schema([
                                NoTimelineDefinedState::make()
                                    ->visible(fn () => ! WeddingTimeline::exists()),

                                WeddingTimelineList::make(),
                            ]),
                    ]),
            ]);
    }
}
