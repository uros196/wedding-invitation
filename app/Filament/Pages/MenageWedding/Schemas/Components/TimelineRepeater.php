<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas\Components;

use App\Rules\ChronologicalOrderRule;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

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

                        Select::make('icon')
                            ->label(__('Icon'))
                            ->placeholder(__('Select an icon'))
                            ->native(false)
                            ->searchable()
                            ->allowHtml()
                            ->options(fn (): array => self::getIconOptions())
                            ->getSearchResultsUsing(fn (string $search): array => self::getIconOptions($search))
                            ->getOptionLabelUsing(fn (?string $value): ?string => filled($value) ? Str::headline($value) : null)
                            ->live()
                            ->prefixIcon(fn (?string $state): ?LucideIcon => filled($state) ? LucideIcon::tryFrom($state) : null),
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

    /**
     * Get the selectable Lucide icon options, optionally filtered by a search term.
     *
     * @return array<string, string>
     */
    protected static function getIconOptions(?string $search = null): array
    {
        return collect(LucideIcon::cases())
            ->when(
                filled($search),
                fn ($icons) => $icons->filter(fn (LucideIcon $icon): bool => str_contains($icon->value, Str::slug($search))),
            )
            ->take(50)
            ->mapWithKeys(fn (LucideIcon $icon): array => [$icon->value => self::getIconOptionLabel($icon->value)])
            ->all();
    }

    /**
     * Build the HTML label (icon preview + name) for a single icon option.
     */
    protected static function getIconOptionLabel(string $value): string
    {
        return '<span class="flex items-center gap-2">'
            .svg("lucide-$value", 'h-4 w-4 shrink-0', ['style' => 'width: 24px;'])->toHtml()
            .'<span class="truncate">'.Str::headline($value).'</span>'
            .'</span>';
    }
}
