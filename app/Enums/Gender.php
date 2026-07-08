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
            self::Male => 'Muški',
            self::Female => 'Ženski',
        };
    }
}
