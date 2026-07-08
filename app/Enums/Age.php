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
            self::Adult => 'Odrasli',
            self::Child => 'Dete',
            self::Baby => 'Beba',
        };
    }
}
