<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Age;
use App\Services\GuestService;
use Filament\Widgets\ChartWidget;

class GuestAgeChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    /**
     * Get the widget heading.
     */
    public function getHeading(): string
    {
        return __('widgets.guest_age_chart.heading');
    }

    /**
     * Prepares and returns data for guest age distribution analysis.
     */
    protected function getData(): array
    {
        $ageData = app(GuestService::class)->getCountsByAge();

        $labels = [];
        $counts = [];

        foreach (Age::cases() as $age) {
            $labels[] = $age->getLabel();
            $counts[] = $ageData[$age->value] ?? 0;
        }

        // Handle null (not declared)
        $notDeclaredCount = $ageData[''] ?? $ageData[null] ?? 0;
        if ($notDeclaredCount > 0) {
            $labels[] = __('widgets.guest_age_chart.unknown');
            $counts[] = $notDeclaredCount;
        }

        return [
            'datasets' => [
                [
                    'label' => __('widgets.guest_age_chart.dataset_label'),
                    'data' => $counts,
                    'backgroundColor' => [
                        '#36A2EB',
                        '#FFCE56',
                        '#FF6384',
                        '#9966FF',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
