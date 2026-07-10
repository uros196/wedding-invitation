<?php

declare(strict_types=1);

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
            Stat::make(__('widgets.guest_status.confirmed.label'), $data->confirmedGuestsCount)
                ->description(__('widgets.guest_status.confirmed.description'))
                ->descriptionIcon(Heroicon::CheckCircle)
                ->color('success'),

            Stat::make(__('widgets.guest_status.declined.label'), $data->declinedGuestsCount)
                ->description(__('widgets.guest_status.declined.description'))
                ->descriptionIcon(Heroicon::XCircle)
                ->color('danger'),

            Stat::make(__('widgets.guest_status.pending.label'), $data->pendingGuestsCount)
                ->description(__('widgets.guest_status.pending.description'))
                ->descriptionIcon(Heroicon::Clock)
                ->color('warning'),
        ];
    }
}
