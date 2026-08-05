<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder\Data;

/**
 * Contains the validated logo data and its dimensions for a QR renderer.
 */
final readonly class QrCodeLogo
{
    public function __construct(
        public string $data,
        public string $mimeType,
        public int $width,
        public int $height,
        public ?\GdImage $image = null,
    ) {}
}
