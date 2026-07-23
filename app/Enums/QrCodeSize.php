<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QrCodeSize: int implements HasLabel
{
    case Px_50 = 50;
    case Px_100 = 100;
    case Px_200 = 200;
    case Px_400 = 400;

    /**
     * Retrieves the default option.
     */
    public static function default(): self
    {
        return self::Px_200;
    }

    /**
     * Gets the label for the option.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Px_50 => '50px',
            self::Px_100 => '100px',
            self::Px_200 => '200px',
            self::Px_400 => '400px',
        };
    }
}
