<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QrCodeFormat: string implements HasLabel
{
    case Svg = 'svg';
    case Png = 'png';

    /**
     * Retrieves the default option.
     */
    public static function default(): self
    {
        return self::Svg;
    }

    /**
     * Gets the label for the option.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Svg => 'SVG',
            self::Png => 'PNG',
        };
    }

    /**
     * Retrieves the content type associated with the option.
     */
    public function contentType(): string
    {
        return match ($this) {
            self::Svg => 'image/svg+xml',
            self::Png => 'image/png',
        };
    }

    /**
     * Retrieves the file extension associated with the option.
     */
    public function extension(): string
    {
        return match ($this) {
            self::Svg => 'svg',
            self::Png => 'png',
        };
    }
}
