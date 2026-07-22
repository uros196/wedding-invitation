<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

use App\Enums\Gender;
use App\Services\GuestService;
use Filament\Widgets\ChartWidget;

class GuestGenderChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    /**
     * Get the widget heading.
     */
    public function getHeading(): string
    {
        return __('widgets.guest_gender_chart.heading');
    }

    /**
     * Retrieves data for generating guest-related statistics grouped by gender.
     */
    protected function getData(): array
    {
        $genderData = app(GuestService::class)->getCountsByGender();

        $labels = [];
        $counts = [];

        foreach (Gender::cases() as $gender) {
            $labels[] = $gender->getLabel();
            $counts[] = $genderData[$gender->value] ?? 0;
        }

        // Handle null (not declared)
        $notDeclaredCount = $genderData[''] ?? $genderData[null] ?? 0;
        if ($notDeclaredCount > 0) {
            $labels[] = __('widgets.guest_gender_chart.unknown');
            $counts[] = $notDeclaredCount;
        }

        return [
            'datasets' => [
                [
                    'label' => __('widgets.guest_gender_chart.dataset_label'),
                    'data' => $counts,
                    'backgroundColor' => [
                        '#36A2EB',
                        '#FF6384',
                        '#9966FF',
                    ],
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
        return 'pie';
    }
}
