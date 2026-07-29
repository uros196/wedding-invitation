<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Represents the gender of a guest.
 */
enum Gender: string implements HasLabel
{
    case Male = 'male';
    case Female = 'female';

    /**
     * Gets the label for the gender.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Male => __('Male'),
            self::Female => __('Female'),
        };
    }

    /**
     * Retrieves the chart color associated with the gender category.
     */
    public function chartColor(): string
    {
        return match ($this) {
            self::Male => 'rgba(54, 162, 235, 0.6)',
            self::Female => 'rgba(255, 99, 132, 0.6)',
        };
    }
}
