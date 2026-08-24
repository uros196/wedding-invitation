<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas;

use App\Filament\Wedding\Pages\ManageWedding\EmptyStates\NoTimelineDefinedState;
use App\Filament\Wedding\Pages\ManageWedding\ManageWedding;
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
use Closure;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;

class Form
{
    /**
     * Define the schema for the wedding management form.
     */
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('Wedding Details'))
                    ->id('wedding-details-tabs')
                    ->activeTab(fn (ManageWedding $livewire): int => match ($livewire->activeTab) {
                        'appearance' => 2,
                        'schedule' => 3,
                        'memory' => 4,
                        'meta' => 5,
                        default => 1,
                    })
                    ->extraAlpineAttributes([
                        'x-on:focus-wedding-tab.window' => 'tab = $event.detail.tab',
                    ])
                    ->tabs([

                        'basic' => Tab::make(__('Basic Information'))
                            ->icon(Heroicon::InformationCircle)
                            ->badge(self::errorBadge(['bride_name', 'groom_name', 'wedding_date', 'rsvp_deadline']))
                            ->schema([
                                Section::make(__('Basic Information'))
                                    ->description(__('wedding.manage_wedding.basic_information.description'))
                                    ->headerActions([
                                        self::helpAction(
                                            'basic_information_help',
                                            __('wedding.manage_wedding.basic_information.help'),
                                        ),
                                    ])
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
                            ]),

                        'appearance' => Tab::make(__('Invitation Look'))
                            ->icon(Heroicon::Photo)
                            ->badge(self::errorBadge(['Hero', 'welcome_text']))
                            ->schema([
                                Section::make(__('Main Image'))
                                    ->description(__('wedding.manage_wedding.main_image_description'))
                                    ->headerActions([
                                        self::helpAction(
                                            'main_image_help',
                                            __('wedding.manage_wedding.main_image_help'),
                                        ),
                                    ])
                                    ->schema([
                                        HeroImageFileUpload::make(),
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
                            ]),

                        'schedule' => Tab::make(__('Schedule'))
                            ->icon(Heroicon::CalendarDays)
                            ->badge(self::errorBadge(['timelines']))
                            ->schema([
                                Section::make(__('Schedule'))
                                    ->description(__('wedding.manage_wedding.schedule.description'))
                                    ->headerActions([
                                        self::helpAction(
                                            'schedule_help',
                                            __('wedding.manage_wedding.schedule.help'),
                                        ),
                                    ])
                                    ->id('wedding-timeline')
                                    ->schema([
                                        NoTimelineDefinedState::make(false),
                                        TimelineRepeater::make(),
                                    ]),
                            ]),

                        'memory' => Tab::make(__('Memory Wall'))
                            ->icon(Heroicon::Photo)
                            ->badge(self::errorBadge(['has_memory_wall', 'memory_wall_open_until']))
                            ->visible(fn (): bool => auth()->user()->can('use-memory-wall', Wedding::class))
                            ->schema([
                                Section::make(__('Memory Wall'))
                                    ->description(__('wedding.manage_wedding.memory_wall.description'))
                                    ->headerActions([
                                        self::helpAction(
                                            'memory_wall_help',
                                            __('wedding.manage_wedding.memory_wall.help'),
                                        ),
                                    ])
                                    ->schema([
                                        MemoryWallToggle::make(),
                                        MemoryWallOpenUntilPicker::make(),
                                        MemoryWallQrCode::make(),
                                        MemoryWallUrlInput::make(),
                                    ]),
                            ]),

                        'meta' => Tab::make(__('Meta Data'))
                            ->icon(Heroicon::Share)
                            ->badge(self::errorBadge(['meta_title', 'meta_description', 'MetaImage']))
                            ->schema([
                                Section::make(__('Meta Data'))
                                    ->description(__('wedding.manage_wedding.meta.description'))
                                    ->headerActions([
                                        self::helpAction(
                                            'meta_help',
                                            __('wedding.manage_wedding.meta.help'),
                                        ),
                                    ])
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
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Display a badge when a tab contains a validation error.
     *
     * @param  array<int, string>  $paths
     */
    private static function errorBadge(array $paths): Closure
    {
        return function (LivewireComponent $livewire) use ($paths): ?string {
            foreach ($livewire->getErrorBag()->keys() as $error) {
                $path = Str::after($error, 'data.');

                foreach ($paths as $prefix) {
                    if ($path === $prefix || Str::startsWith($path, "{$prefix}.")) {
                        return __('Error');
                    }
                }
            }

            return null;
        };
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
