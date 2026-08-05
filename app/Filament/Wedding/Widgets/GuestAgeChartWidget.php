<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

use App\Enums\Age;
use App\Services\GuestService;
use Filament\Widgets\ChartWidget;

class GuestAgeChartWidget extends ChartWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 6;

    /**
     * Get the widget heading.
     */
    public function getHeading(): string
    {
        return __('wedding.widgets.guest_age_chart.heading');
    }

    /**
     * Prepares and returns data for guest age distribution analysis.
     */
    protected function getData(): array
    {
        $ageData = app(GuestService::class)->getCountsByAge();

        $labels = [];
        $counts = [];
        $colors = [];

        foreach (Age::cases() as $age) {
            $labels[] = $age->getLabel();
            $counts[] = $ageData[$age->value] ?? 0;
            $colors[] = $age->chartColor();
        }

        // Handle null (not declared)
        $notDeclaredCount = $ageData[''] ?? $ageData[null] ?? 0;
        if ($notDeclaredCount > 0) {
            $labels[] = __('Unknown');
            $counts[] = $notDeclaredCount;
            $colors[] = 'rgba(156, 163, 175, 0.35)';
        }

        return [
            'datasets' => [
                [
                    'label' => __('Guests'),
                    'data' => $counts,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }
}
