<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder\Data;

/**
 * Holds centered coordinates for a logo and its circular quiet zone.
 */
final readonly class QrCodeLogoPlacement
{
    public function __construct(
        public float $centerX,
        public float $centerY,
        public float $logoX,
        public float $logoY,
        public int $logoSize,
        public int $margin,
        public float $containerDiameter,
        public float $containerRadius,
    ) {}
}
