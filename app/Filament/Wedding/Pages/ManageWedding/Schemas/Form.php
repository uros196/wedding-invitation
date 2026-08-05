<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas;

use App\Filament\Wedding\Pages\ManageWedding\EmptyStates\NoTimelineDefinedState;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\BrideNameInput;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\GroomNameInput;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\HeroImageFileUpload;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\MemoryWallOpenUntilPicker;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\MemoryWallQrCode;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\MemoryWallToggle;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\MemoryWallUrlInput;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\MetaDescriptionTextarea;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\MetaImageFileUpload;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\MetaTitleInput;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\RSVPDeadlinePicker;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\TimelineRepeater;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\WeddingDatePicker;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Components\WelcomeTextRichEditor;
use App\Models\Wedding;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
                        Section::make(__('Basic Information'))
                            ->description(__('wedding.manage_wedding.basic_information.description'))
                            ->headerActions([
                                self::helpAction(
                                    'basic_information_help',
                                    __('wedding.manage_wedding.basic_information.help'),
                                ),
                            ])
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
                            ->description(__('wedding.manage_wedding.main_image_description'))
                            ->headerActions([
                                self::helpAction(
                                    'main_image_help',
                                    __('wedding.manage_wedding.main_image_help'),
                                ),
                            ])
                            ->columnSpan(1)
                            ->schema([
                                HeroImageFileUpload::make(),
                            ]),
                    ]),

                Section::make(__('Invitation Text'))
                    ->description(__('wedding.manage_wedding.invitation_text.description'))
                    ->headerActions([
                        self::helpAction(
                            'invitation_text_help',
                            __('wedding.manage_wedding.invitation_text.help'),
                        ),
                    ])
                    ->schema([
                        WelcomeTextRichEditor::make(),
                    ]),

                Section::make(__('Schedule'))
                    ->description(__('wedding.manage_wedding.schedule.description'))
                    ->headerActions([
                        self::helpAction(
                            'schedule_help',
                            __('wedding.manage_wedding.schedule.help'),
                        ),
                    ])
                    ->id('wedding-timeline')
                    ->extraAlpineAttributes([
                        'x-init' => <<<'JS'
                            if (window.location.hash === '#wedding-timeline') {
                                $nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start' }))
                            }
                            JS,
                    ])
                    ->schema([
                        NoTimelineDefinedState::make(false),
                        TimelineRepeater::make(),
                    ]),

                Grid::make([
                    'default' => 1,
                    'lg' => 3,
                ])
                    ->schema([
                        Section::make(__('Memory Wall'))
                            ->description(__('wedding.manage_wedding.memory_wall.description'))
                            ->headerActions([
                                self::helpAction(
                                    'memory_wall_help',
                                    __('wedding.manage_wedding.memory_wall.help'),
                                ),
                            ])
                            ->visible(fn () => auth()->user()->can('use-memory-wall', Wedding::class))
                            ->columnSpan(1)
                            ->schema([
                                MemoryWallToggle::make(),
                                MemoryWallOpenUntilPicker::make(),
                                MemoryWallQrCode::make(),
                                MemoryWallUrlInput::make(),
                            ]),

                        Section::make(__('Meta Data'))
                            ->description(__('wedding.manage_wedding.meta.description'))
                            ->headerActions([
                                self::helpAction(
                                    'meta_help',
                                    __('wedding.manage_wedding.meta.help'),
                                ),
                            ])
                            ->columnSpan(
                                fn (): int => auth()->user()->can('use-memory-wall', Wedding::class) ? 2 : 3,
                            )
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
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Create an accessible info action for a section header.
     */
    private static function helpAction(string $name, string $tooltip): Action
    {
        return Action::make($name)
            ->label(__('wedding.manage_wedding.help_action'))
            ->icon(Heroicon::InformationCircle)
            ->iconButton()
            ->color('gray')
            ->tooltip($tooltip);
    }
}
