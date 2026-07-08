<?php

namespace App\Filament\Widgets;

use App\Enums\Gender;
use App\Services\GuestService;
use Filament\Widgets\ChartWidget;

class GuestGenderChartWidget extends ChartWidget
{
    protected ?string $heading = 'Distribucija po polu';

    protected static ?int $sort = 5;

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
            $labels[] = 'Nepoznato';
            $counts[] = $notDeclaredCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Gosti',
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

    protected function getType(): string
    {
        return 'pie';
    }
}
