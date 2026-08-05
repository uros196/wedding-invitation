<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages;

use App\Filament\Wedding\Widgets\GroupViewsWidget;
use App\Filament\Wedding\Widgets\GuestAgeChartWidget;
use App\Filament\Wedding\Widgets\GuestDemographicsWidget;
use App\Filament\Wedding\Widgets\GuestGenderChartWidget;
use App\Filament\Wedding\Widgets\GuestStatusWidget;
use App\Filament\Wedding\Widgets\InvitationStats;
use App\Filament\Wedding\Widgets\WeddingStatusWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Configure the dashboard grid for a compact, responsive layout.
     */
    public function getColumns(): int|array
    {
        return ['md' => 2];
    }

    /**
     * Return dashboard widgets in the order users need them.
     *
     * @return array<int, class-string>
     */
    public function getWidgets(): array
    {
        return [
            WeddingStatusWidget::class,
            InvitationStats::class,
            GuestStatusWidget::class,
            GuestDemographicsWidget::class,
            GroupViewsWidget::class,
            GuestAgeChartWidget::class,
            GuestGenderChartWidget::class,
        ];
    }
}
