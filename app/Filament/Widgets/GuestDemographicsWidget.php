<?php

namespace App\Filament\Widgets;

use App\Enums\Age;
use App\Enums\Gender;
use App\Services\GuestService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GuestDemographicsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    /**
     * Retrieves statistics about guests, including total count, age structure, and gender structure.
     */
    protected function getStats(): array
    {
        $data = app(GuestService::class)->getGroupedCounts();

        $adults = $data->guestsByAge[Age::Adult->value] ?? 0;
        $children = $data->guestsByAge[Age::Child->value] ?? 0;
        $babies = $data->guestsByAge[Age::Baby->value] ?? 0;

        $males = $data->guestsByGender[Gender::Male->value] ?? 0;
        $females = $data->guestsByGender[Gender::Female->value] ?? 0;

        return [
            Stat::make('Ukupno gostiju', $data->totalGuestsCount)
                ->description('Ukupan broj svih gostiju u bazi')
                ->descriptionIcon(Heroicon::Users)
                ->color('info'),

            Stat::make('Struktura po uzrastu', "{$adults} / {$children} / {$babies}")
                ->description('Odrasli / Deca / Bebe')
                ->descriptionIcon(Heroicon::UserGroup)
                ->color('success'),

            Stat::make('Struktura po polu', "{$males} / {$females}")
                ->description('Muški / Ženski')
                ->descriptionIcon(Heroicon::Variable)
                ->color('warning'),
        ];
    }
}
