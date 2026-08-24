<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

use App\Filament\Wedding\Pages\SetupWedding\SetupWedding;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeddingSetupWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 0;

    /**
     * Display the setup shortcut until the wedding is published.
     */
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->team !== null
            && ! $user->hasPublishedWedding();
    }

    /**
     * Get the setup widget heading.
     */
    protected function getHeading(): ?string
    {
        return __('wedding.widgets.wedding_setup.heading');
    }

    /**
     * Get the setup shortcut.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make(__('wedding.widgets.wedding_setup.stat'), __('wedding.widgets.wedding_setup.status'))
                ->description(__('wedding.widgets.wedding_setup.description'))
                ->descriptionIcon(Heroicon::ArrowRight)
                ->color('primary')
                ->url(SetupWedding::getUrl()),
        ];
    }
}
