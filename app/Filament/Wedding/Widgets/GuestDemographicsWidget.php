<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

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

        $adults     = $data->guestsByAge[Age::Adult->value] ?? 0;
        $children   = $data->guestsByAge[Age::Child->value] ?? 0;
        $babies     = $data->guestsByAge[Age::Baby->value] ?? 0;

        $males      = $data->guestsByGender[Gender::Male->value] ?? 0;
        $females    = $data->guestsByGender[Gender::Female->value] ?? 0;

        return [
            Stat::make(__('widgets.guest_demographics.total_guests.label'), $data->totalGuestsCount)
                ->description(__('widgets.guest_demographics.total_guests.description'))
                ->descriptionIcon(Heroicon::Users)
                ->color('info'),

            Stat::make(__('widgets.guest_demographics.age_structure.label'), "{$adults} / {$children} / {$babies}")
                ->description(__('widgets.guest_demographics.age_structure.description'))
                ->descriptionIcon(Heroicon::UserGroup)
                ->color('success'),

            Stat::make(__('widgets.guest_demographics.gender_structure.label'), "{$males} / {$females}")
                ->description(__('widgets.guest_demographics.gender_structure.description'))
                ->descriptionIcon(Heroicon::Variable)
                ->color('warning'),
        ];
    }
}
