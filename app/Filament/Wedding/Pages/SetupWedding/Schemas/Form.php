<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\SetupWedding\Schemas;

use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\BrideNameInput;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\GroomNameInput;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\HeroImageFileUpload;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\RSVPDeadlinePicker;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\WeddingDatePicker;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\WelcomeTextRichEditor;
use App\Filament\Wedding\Pages\SetupWedding\SetupWedding;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

class Form
{
    /**
     * Configure the setup wizard schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make(__('wedding.setup.steps.basics.title'))
                        ->description(__('wedding.setup.steps.basics.description'))
                        ->schema([
                            Section::make(__('wedding.setup.steps.basics.heading'))
                                ->description(__('wedding.setup.steps.basics.help'))
                                ->schema([
                                    BrideNameInput::make(),
                                    GroomNameInput::make(),
                                    WeddingDatePicker::make(),
                                ]),
                        ])
                        ->afterValidation(function (Step $component, SetupWedding $livewire): void {
                            $data = Arr::only($component->getState(), ['bride_name', 'groom_name', 'wedding_date']);
                            $livewire->saveDraft($data);
                        }),

                    Step::make(__('wedding.setup.steps.appearance.title'))
                        ->description(__('wedding.setup.steps.appearance.description'))
                        ->schema([
                            Section::make(__('wedding.setup.steps.appearance.heading'))
                                ->description(__('wedding.setup.steps.appearance.help'))
                                ->schema([
                                    HeroImageFileUpload::make(),
                                    WelcomeTextRichEditor::make(),
                                ]),
                        ])
                        ->afterValidation(function (Step $component, SetupWedding $livewire): void {
                            $data = Arr::only($component->getState(), ['welcome_text', 'Hero']);
                            $livewire->saveDraft($data);
                        }),

                    Step::make(__('wedding.setup.steps.guest_info.title'))
                        ->description(__('wedding.setup.steps.guest_info.description'))
                        ->schema([
                            Section::make(__('wedding.setup.steps.guest_info.heading'))
                                ->description(__('wedding.setup.steps.guest_info.help'))
                                ->schema([
                                    RSVPDeadlinePicker::make()->hintIcon(null),
                                ]),
                        ]),
                ])
                    ->skippable(false)
                    ->submitAction(Action::make('publish')
                        ->label(__('wedding.setup.publish_action'))
                        ->color('success')
                        ->submit('publish')),
            ])
            ->statePath('data');
    }
}
