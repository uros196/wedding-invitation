<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder\Render;

use App\Enums\QrCodeFormat;
use App\Support\QrCodeBuilder\QrCodeGeometry;
use App\Support\QrCodeBuilder\QrCodeLogoLoader;
use App\Support\QrCodeBuilder\QrCodeStyle;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\Writer\Result\PngResult;

/**
 * Renders the wedding QR matrix as a GD-backed PNG image.
 */
final readonly class PngQrCodeRenderer
{
    public function __construct(
        private QrCodeLogoLoader $logoLoader,
        private QrCodeGeometry $geometry,
    ) {}

    /**
     * Render the QR matrix with raster modules, rounded finders, and a logo.
     */
    public function render(MatrixInterface $matrix, QrCodeStyle $style): PngResult
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('Unable to generate PNG QR code: the GD extension is not enabled.');
        }

        $outerSize = max(1, $matrix->getOuterSize());
        $image = imagecreatetruecolor($outerSize, $outerSize);

        if (! $image instanceof \GdImage) {
            throw new \RuntimeException('Unable to create the PNG QR code image.');
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $backgroundColor = $this->allocateColor($image, $style->backgroundColor);
        imagefill($image, 0, 0, $backgroundColor);
        imagealphablending($image, true);

        $foregroundColor = $this->allocateColor($image, $style->foregroundColor);
        $this->renderModules($image, $matrix, $style, $foregroundColor);
        $this->renderFinderPatterns($image, $matrix, $style, $foregroundColor, $backgroundColor);
        $this->renderLogo($image, $matrix, $style, $backgroundColor);

        return new PngResult($matrix, $image);
    }

    /**
     * Render non-finder dark modules as circles.
     */
    private function renderModules(
        \GdImage $image,
        MatrixInterface $matrix,
        QrCodeStyle $style,
        int $foregroundColor,
    ): void {
        $blockCount = $matrix->getBlockCount();
        $blockSize = $matrix->getBlockSize();
        $diameter = max(1, (int) round(min($blockSize, $blockSize * $style->dotScale)));
        $finderOrigins = $this->geometry->finderOrigins($blockCount);

        for ($row = 0; $row < $blockCount; $row++) {
            for ($column = 0; $column < $blockCount; $column++) {
                if (
                    $matrix->getBlockValue($row, $column) !== 1
                    || $this->geometry->isFinderPatternCell($row, $column, $finderOrigins)
                ) {
                    continue;
                }

                [$centerX, $centerY] = $this->geometry->moduleCenter($matrix, $row, $column);
                imagefilledellipse(
                    $image,
                    (int) round($centerX),
                    (int) round($centerY),
                    $diameter,
                    $diameter,
                    $foregroundColor,
                );
            }
        }
    }

    /**
     * Render the three finder patterns with rounded corners.
     */
    private function renderFinderPatterns(
        \GdImage $image,
        MatrixInterface $matrix,
        QrCodeStyle $style,
        int $foregroundColor,
        int $backgroundColor,
    ): void {
        $blockSize = $matrix->getBlockSize();
        $finderSize = 7 * $blockSize;
        $radius = min($finderSize / 2, $blockSize * $style->finderRadius);
        $innerRadius = $radius * 0.65;
        $centerRadius = $radius * 0.45;

        foreach ($this->geometry->finderOrigins($matrix->getBlockCount()) as [$row, $column]) {
            $x = $matrix->getMarginLeft() + $column * $blockSize;
            $y = $matrix->getMarginLeft() + $row * $blockSize;
            $this->drawRoundedRectangle($image, $x, $y, $finderSize, $finderSize, $radius, $foregroundColor);
            $this->drawRoundedRectangle(
                $image,
                $x + $blockSize,
                $y + $blockSize,
                5 * $blockSize,
                5 * $blockSize,
                $innerRadius,
                $backgroundColor,
            );
            $this->drawRoundedRectangle(
                $image,
                $x + 2 * $blockSize,
                $y + 2 * $blockSize,
                3 * $blockSize,
                3 * $blockSize,
                $centerRadius,
                $foregroundColor,
            );
        }
    }

    /**
     * Place the raster favicon over a circular, opaque quiet zone.
     */
    private function renderLogo(
        \GdImage $image,
        MatrixInterface $matrix,
        QrCodeStyle $style,
        int $backgroundColor,
    ): void {
        $logo = $this->logoLoader->load($style, QrCodeFormat::Png, $matrix->getInnerSize());

        if (! $logo->image instanceof \GdImage) {
            throw new \RuntimeException('The QR code logo must be a raster image for PNG output.');
        }

        $placement = $this->geometry->logoPlacement($matrix, $style);
        imagefilledellipse(
            $image,
            (int) round($placement->centerX),
            (int) round($placement->centerY),
            (int) round($placement->containerDiameter),
            (int) round($placement->containerDiameter),
            $backgroundColor,
        );
        imagecopyresampled(
            $image,
            $logo->image,
            (int) round($placement->logoX),
            (int) round($placement->logoY),
            0,
            0,
            $placement->logoSize,
            $placement->logoSize,
            imagesx($logo->image),
            imagesy($logo->image),
        );
    }

    /**
     * Allocate a GD color from an Endroid color value.
     */
    private function allocateColor(\GdImage $image, ColorInterface $color): int
    {
        $allocatedColor = imagecolorallocatealpha(
            $image,
            $color->getRed(),
            $color->getGreen(),
            $color->getBlue(),
            $color->getAlpha(),
        );

        if ($allocatedColor === false) {
            throw new \RuntimeException('Unable to allocate a QR code color.');
        }

        return $allocatedColor;
    }

    /**
     * Draw a filled rounded rectangle for a finder-pattern layer.
     */
    private function drawRoundedRectangle(
        \GdImage $image,
        float $x,
        float $y,
        float $width,
        float $height,
        float $radius,
        int $color,
    ): void {
        $left = (int) round($x);
        $top = (int) round($y);
        $right = (int) round($x + $width);
        $bottom = (int) round($y + $height);
        $radius = (int) round(min($radius, $width / 2, $height / 2));

        if ($radius <= 0) {
            imagefilledrectangle($image, $left, $top, $right, $bottom, $color);

            return;
        }

        imagefilledrectangle($image, $left + $radius, $top, $right - $radius, $bottom, $color);
        imagefilledrectangle($image, $left, $top + $radius, $right, $bottom - $radius, $color);

        $diameter = 2 * $radius;
        imagefilledellipse($image, $left + $radius, $top + $radius, $diameter, $diameter, $color);
        imagefilledellipse($image, $right - $radius, $top + $radius, $diameter, $diameter, $color);
        imagefilledellipse($image, $left + $radius, $bottom - $radius, $diameter, $diameter, $color);
        imagefilledellipse($image, $right - $radius, $bottom - $radius, $diameter, $diameter, $color);
    }
}
