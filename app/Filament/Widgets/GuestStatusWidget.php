<?php

namespace App\Filament\Widgets;

use App\Services\GuestService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GuestStatusWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    /**
     * Retrieves statistical data related to guest confirmations, declines, and pending responses.
     */
    protected function getStats(): array
    {
        $data = app(GuestService::class)->getStatusCounts();

        return [
            Stat::make('Potvrđeno', $data->confirmedGuestsCount)
                ->description('Gosti koji dolaze')
                ->descriptionIcon(Heroicon::CheckCircle)
                ->color('success'),

            Stat::make('Odbijeno', $data->declinedGuestsCount)
                ->description('Gosti koji ne dolaze')
                ->descriptionIcon(Heroicon::XCircle)
                ->color('danger'),

            Stat::make('Na čekanju', $data->pendingGuestsCount)
                ->description('Gosti koji se još nisu izjasnili')
                ->descriptionIcon(Heroicon::Clock)
                ->color('warning'),
        ];
    }
}
