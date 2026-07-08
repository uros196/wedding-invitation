<?php

namespace App\Filament\Widgets;

use App\Services\WeddingService;
use App\Support\Countdown;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget that displays basic statistics about invitations and guests.
 */
class InvitationStats extends StatsOverviewWidget
{
    /**
     * The sort order of the widget on the dashboard.
     */
    protected static ?int $sort = 1;

    /**
     * Get the stats to be displayed on the dashboard.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $data = app(WeddingService::class)->getInvitationStats();

        $stats = [
            Stat::make('Poslate pozivnice', $data->sentInvitationsCount)
                ->description('Ukupan broj grupa kojima je poslata pozivnica')
                ->descriptionIcon(Heroicon::PaperAirplane)
                ->color('info'),

            Stat::make('Ukupno pregleda', $data->totalViews)
                ->description('Koliko puta su sve pozivnice ukupno otvorene')
                ->descriptionIcon(Heroicon::Eye)
                ->color('warning'),
        ];

        if (filled($weddingCountdown = $data->wedding?->wedding_countdown)) {
            $stats[] = Stat::make($this->weddingCountdownLabel($weddingCountdown), $weddingCountdown->value())
                ->description($weddingCountdown->datetime())
                ->descriptionIcon(Heroicon::CalendarDays)
                ->color('primary');
        }

        if (filled($rsvpCountdown = $data->wedding?->rsvp_countdown)) {
            $stats[] = Stat::make($this->rsvpCountdownLabel($rsvpCountdown), $rsvpCountdown->value())
                ->description($rsvpCountdown->datetime())
                ->descriptionIcon(Heroicon::Clock)
                ->color($rsvpCountdown->isPast() ? 'danger' : 'success');
        }

        return $stats;
    }

    /**
     * Get the wedding countdown label.
     */
    protected function weddingCountdownLabel(Countdown $countdown): string
    {
        return $countdown->isPast()
            ? __(config('wedding.widgets.countdown.label.wedding_past'))
            : __(config('wedding.widgets.countdown.label.wedding_until'));
    }

    /**
     * Get the RSVP countdown label.
     */
    protected function rsvpCountdownLabel(Countdown $countdown): string
    {
        return $countdown->isPast()
            ? __(config('wedding.widgets.countdown.label.application_past'))
            : __(config('wedding.widgets.countdown.label.application_until'));
    }
}
