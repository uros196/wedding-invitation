<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder;

use App\Enums\QrCodeFormat;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

/**
 * Describes the visual rules and assets used to render a QR code.
 */
final readonly class QrCodeStyle
{
    public function __construct(
        public ColorInterface $foregroundColor,
        public ColorInterface $backgroundColor,
        public ErrorCorrectionLevel $errorCorrectionLevel,
        public RoundBlockSizeMode $roundBlockSizeMode,
        public int $margin,
        public string $logoPath,
        public string $rasterLogoPath,
        public float $dotScale,
        public float $finderRadius,
        public float $logoSizeRatio,
        public float $logoMarginRatio,
        public int $minimumLogoSize,
        public int $maximumLogoSize,
        public int $minimumLogoMargin,
        public int $maximumLogoMargin,
    ) {}

    /**
     * Calculate the logo size for the given inner QR code size.
     */
    public function logoSize(int $size): int
    {
        return min(
            $this->maximumLogoSize,
            max($this->minimumLogoSize, (int) round($size * $this->logoSizeRatio)),
        );
    }

    /**
     * Calculate the quiet-zone size around the centered logo.
     */
    public function logoMargin(int $size): int
    {
        return min(
            $this->maximumLogoMargin,
            max($this->minimumLogoMargin, (int) round($size * $this->logoMarginRatio)),
        );
    }

    /**
     * Resolve the logo asset appropriate for the requested output format.
     */
    public function logoPathFor(QrCodeFormat $format): string
    {
        return $format === QrCodeFormat::Png ? $this->rasterLogoPath : $this->logoPath;
    }
}
