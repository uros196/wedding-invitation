<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder\Render;

use App\Enums\QrCodeFormat;
use App\Support\QrCodeBuilder\Data\QrCodeLogo;
use App\Support\QrCodeBuilder\QrCodeGeometry;
use App\Support\QrCodeBuilder\QrCodeLogoLoader;
use App\Support\QrCodeBuilder\QrCodeStyle;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\Writer\Result\SvgResult;

/**
 * Renders the wedding QR matrix as a styled, self-contained SVG document.
 */
final readonly class SvgQrCodeRenderer
{
    public function __construct(
        private QrCodeLogoLoader $logoLoader,
        private QrCodeGeometry $geometry,
    ) {}

    /**
     * Render the QR matrix with circular modules, rounded finders, and a logo.
     */
    public function render(MatrixInterface $matrix, QrCodeStyle $style): SvgResult
    {
        $logo = $this->logoLoader->load($style, QrCodeFormat::Svg, $matrix->getInnerSize());
        $outerSize = $matrix->getOuterSize();
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="%spx" height="%spx" viewBox="0 0 %s %s">',
            $this->formatNumber($outerSize),
            $this->formatNumber($outerSize),
            $this->formatNumber($outerSize),
            $this->formatNumber($outerSize),
        );
        $svg .= sprintf(
            '<rect x="0" y="0" width="%s" height="%s" %s/>',
            $this->formatNumber($outerSize),
            $this->formatNumber($outerSize),
            $this->colorAttributes($style->backgroundColor),
        );
        $svg .= $this->renderModules($matrix, $style);
        $svg .= $this->renderFinderPatterns($matrix, $style);
        $svg .= $this->renderLogo($matrix, $style, $logo);
        $svg .= '</svg>';

        return new SvgResult($matrix, new \SimpleXMLElement($svg), true);
    }

    /**
     * Render non-finder dark modules as circles.
     */
    private function renderModules(MatrixInterface $matrix, QrCodeStyle $style): string
    {
        $modules = '';
        $blockCount = $matrix->getBlockCount();
        $blockSize = $matrix->getBlockSize();
        $radius = min($blockSize / 2, $blockSize * $style->dotScale / 2);
        $foregroundAttributes = $this->colorAttributes($style->foregroundColor);
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
                $modules .= sprintf(
                    '<circle cx="%s" cy="%s" r="%s" %s/>',
                    $this->formatNumber($centerX),
                    $this->formatNumber($centerY),
                    $this->formatNumber($radius),
                    $foregroundAttributes,
                );
            }
        }

        return $modules;
    }

    /**
     * Render the three finder patterns with rounded corners.
     */
    private function renderFinderPatterns(MatrixInterface $matrix, QrCodeStyle $style): string
    {
        $patterns = '';
        $blockSize = $matrix->getBlockSize();
        $finderSize = 7 * $blockSize;
        $radius = min($finderSize / 2, $blockSize * $style->finderRadius);
        $innerRadius = $radius * 0.65;
        $centerRadius = $radius * 0.45;
        $foregroundAttributes = $this->colorAttributes($style->foregroundColor);
        $backgroundAttributes = $this->colorAttributes($style->backgroundColor);

        foreach ($this->geometry->finderOrigins($matrix->getBlockCount()) as [$row, $column]) {
            $x = $matrix->getMarginLeft() + $column * $blockSize;
            $y = $matrix->getMarginLeft() + $row * $blockSize;
            $patterns .= $this->roundedRectangle($x, $y, $finderSize, $finderSize, $radius, $foregroundAttributes);
            $patterns .= $this->roundedRectangle(
                $x + $blockSize,
                $y + $blockSize,
                5 * $blockSize,
                5 * $blockSize,
                $innerRadius,
                $backgroundAttributes,
            );
            $patterns .= $this->roundedRectangle(
                $x + 2 * $blockSize,
                $y + 2 * $blockSize,
                3 * $blockSize,
                3 * $blockSize,
                $centerRadius,
                $foregroundAttributes,
            );
        }

        return $patterns;
    }

    /**
     * Render the vector favicon over a circular, opaque quiet zone.
     */
    private function renderLogo(MatrixInterface $matrix, QrCodeStyle $style, QrCodeLogo $logo): string
    {
        $placement = $this->geometry->logoPlacement($matrix, $style);
        $backgroundAttributes = $this->colorAttributes($style->backgroundColor);
        $dataUri = 'data:'.$logo->mimeType.';base64,'.base64_encode($logo->data);

        return sprintf(
            '<circle cx="%s" cy="%s" r="%s" %s/>',
            $this->formatNumber($placement->centerX),
            $this->formatNumber($placement->centerY),
            $this->formatNumber($placement->containerRadius),
            $backgroundAttributes,
        ).sprintf(
            '<image x="%s" y="%s" width="%s" height="%s" preserveAspectRatio="xMidYMid meet" href="%s"/>',
            $this->formatNumber($placement->logoX),
            $this->formatNumber($placement->logoY),
            $this->formatNumber($placement->logoSize),
            $this->formatNumber($placement->logoSize),
            htmlspecialchars($dataUri, ENT_QUOTES | ENT_XML1, 'UTF-8'),
        );
    }

    /**
     * Build a rounded SVG rectangle for finder-pattern layers.
     */
    private function roundedRectangle(
        float $x,
        float $y,
        float $width,
        float $height,
        float $radius,
        string $colorAttributes,
    ): string {
        return sprintf(
            '<rect x="%s" y="%s" width="%s" height="%s" rx="%s" %s/>',
            $this->formatNumber($x),
            $this->formatNumber($y),
            $this->formatNumber($width),
            $this->formatNumber($height),
            $this->formatNumber($radius),
            $colorAttributes,
        );
    }

    /**
     * Convert a QR color into SVG attributes.
     */
    private function colorAttributes(ColorInterface $color): string
    {
        $attributes = 'fill="'.$color->getHex().'"';

        if ($color->getOpacity() < 1) {
            $attributes .= ' fill-opacity="'.$this->formatNumber($color->getOpacity()).'"';
        }

        return $attributes;
    }

    /**
     * Format a number without locale-specific decimal separators.
     */
    private function formatNumber(float|int $number): string
    {
        $formatted = number_format((float) $number, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    }
}
