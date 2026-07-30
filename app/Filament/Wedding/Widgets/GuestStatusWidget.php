<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

use App\Models\Group;
use App\Services\GuestService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class GuestStatusWidget extends StatsOverviewWidget
{
    public ?Group $group = null;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    /**
     * Retrieves statistical data related to guest confirmations, declines, and pending responses.
     */
    protected function getStats(): array
    {
        $data = app(GuestService::class)->getStatusCounts($this->group);

        return [
            Stat::make(__('Confirmed'), $data->confirmedGuestsCount)
                ->description(__('wedding.widgets.guest_status.confirmed.description'))
                ->descriptionIcon(Heroicon::CheckCircle)
                ->color('success'),

            Stat::make(__('Declined'), $data->declinedGuestsCount)
                ->description(__('wedding.widgets.guest_status.declined.description'))
                ->descriptionIcon(Heroicon::XCircle)
                ->color('danger'),

            Stat::make(__('Pending'), $data->pendingGuestsCount)
                ->description(__('wedding.widgets.guest_status.pending.description'))
                ->descriptionIcon(Heroicon::Clock)
                ->color('warning'),
        ];
    }

    #[On('refresh-guest-status-widget')]
    public function refreshStats(): void
    {
        $this->dispatch('$refresh');
    }
}
