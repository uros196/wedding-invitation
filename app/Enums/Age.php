<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Represents the age category of a guest.
 */
enum Age: string implements HasLabel
{
    case Adult = 'adult';
    case Child = 'child';
    case Baby = 'baby';

    /**
     * Gets the label for the age category.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Adult => __('Adult'),
            self::Child => __('Child'),
            self::Baby => __('Baby'),
        };
    }

    /**
     * Retrieves the chart color associated with the age category.
     */
    public function chartColor(): string
    {
        return match ($this) {
            self::Adult => 'rgba(54, 162, 235, 0.6)',
            self::Child => 'rgba(255, 206, 86, 0.6)',
            self::Baby => 'rgba(255, 99, 132, 0.6)',
        };
    }
}
