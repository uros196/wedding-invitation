<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas;

use App\Filament\Pages\MenageWedding\EmptyStates\NoTimelineDefinedState;
use App\Filament\Pages\MenageWedding\Schemas\Components\BrideNameInput;
use App\Filament\Pages\MenageWedding\Schemas\Components\GroomNameInput;
use App\Filament\Pages\MenageWedding\Schemas\Components\HeroImageFileUpload;
use App\Filament\Pages\MenageWedding\Schemas\Components\MetaDescriptionTextarea;
use App\Filament\Pages\MenageWedding\Schemas\Components\MetaImageFileUpload;
use App\Filament\Pages\MenageWedding\Schemas\Components\MetaTitleInput;
use App\Filament\Pages\MenageWedding\Schemas\Components\RSVPDeadlinePicker;
use App\Filament\Pages\MenageWedding\Schemas\Components\TimelineRepeater;
use App\Filament\Pages\MenageWedding\Schemas\Components\WeddingDatePicker;
use App\Filament\Pages\MenageWedding\Schemas\Components\WelcomeTextRichEditor;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Form
{
    /**
     * Define the schema for the wedding management form.
     */
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make(__('messages.basic_info'))
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        BrideNameInput::make(),
                                        GroomNameInput::make(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        WeddingDatePicker::make(),
                                        RSVPDeadlinePicker::make(),
                                    ]),
                            ]),

                        Section::make(__('Main Image'))
                            ->description(__('This image is displayed at the top of the invitation.'))
                            ->columnSpan(1)
                            ->schema([
                                HeroImageFileUpload::make(),
                            ]),
                    ]),

                Section::make(__('Invitation Text'))
                    ->schema([
                        WelcomeTextRichEditor::make(),
                    ]),

                Section::make(__('Schedule'))
                    ->schema([
                        NoTimelineDefinedState::make(false),
                        TimelineRepeater::make(),
                    ]),

                Section::make(__('Meta Data'))
                    ->description(__('Used to generate the link preview when the invitation is shared.'))
                    ->columns(3)
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                MetaTitleInput::make(),
                                MetaDescriptionTextarea::make(),
                            ]),

                        Grid::make(1)
                            ->schema([
                                MetaImageFileUpload::make(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
}
