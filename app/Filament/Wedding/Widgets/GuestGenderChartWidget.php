<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

use App\Enums\Gender;
use App\Services\GuestService;
use Filament\Widgets\ChartWidget;

class GuestGenderChartWidget extends ChartWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 7;

    /**
     * Get the widget heading.
     */
    public function getHeading(): string
    {
        return __('wedding.widgets.guest_gender_chart.heading');
    }

    /**
     * Retrieves data for generating guest-related statistics grouped by gender.
     */
    protected function getData(): array
    {
        $genderData = app(GuestService::class)->getCountsByGender();

        $labels = [];
        $counts = [];
        $colors = [];

        foreach (Gender::cases() as $gender) {
            $labels[] = $gender->getLabel();
            $counts[] = $genderData[$gender->value] ?? 0;
            $colors[] = $gender->chartColor();
        }

        // Handle null (not declared)
        $notDeclaredCount = $genderData[''] ?? $genderData[null] ?? 0;
        if ($notDeclaredCount > 0) {
            $labels[] = __('Unknown');
            $counts[] = $notDeclaredCount;
            $colors[] = 'rgba(156, 163, 175, 0.6)';
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

    /**
     * Get the chart type.
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}
