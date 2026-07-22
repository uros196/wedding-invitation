<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\MenageWedding\Schemas\Components;

use App\Filament\Schemas\Components\IconSelect;
use App\Rules\ChronologicalOrderRule;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

class TimelineRepeater
{
    /**
     * Generate the repeater for the wedding timeline.
     */
    public static function make(): Repeater
    {
        return Repeater::make('timelines')
            ->relationship('timelines')
            ->label(__('Timeline'))
            ->addActionLabel(__('Add Timeline'))
            ->rules([new ChronologicalOrderRule])
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Event Name'))
                            ->required(),

                        TimePicker::make('time')
                            ->label(__('Time'))
                            ->seconds(false)
                            ->required(),

                        IconSelect::make('icon'),
                    ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('address')
                            ->label(__('Address')),

                        TextInput::make('map_url')
                            ->label(__('Map Link'))
                            ->url()
                            ->suffixAction(
                                Action::make('open_map')
                                    ->icon(Heroicon::ArrowTopRightOnSquare)
                                    ->url(fn (?string $state): ?string => $state)
                                    ->openUrlInNewTab()
                                    ->visible(fn (?string $state): bool => filled($state)),
                            ),
                    ]),
                Grid::make(2)
                    ->schema([
                        Toggle::make('is_visible')
                            ->label(__('Visible'))
                            ->default(true),
                    ]),
            ])
            ->columns(1)
            ->reorderable()
            ->orderColumn('sort_order')
            ->defaultItems(0)
            ->live()
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null);
    }
}
