<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder;

use App\Enums\QrCodeFormat;
use App\Support\QrCodeBuilder\Render\PngQrCodeRenderer;
use App\Support\QrCodeBuilder\Render\SvgQrCodeRenderer;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * Coordinates style selection, matrix creation, and format-specific rendering.
 */
final readonly class QrCodeBuilder
{
    public function __construct(
        private QrCodeMatrixBuilder $matrixBuilder,
        private SvgQrCodeRenderer $svgRenderer,
        private PngQrCodeRenderer $pngRenderer,
    ) {}

    /**
     * Build a QR code for the wedding invitation in the requested format.
     */
    public function build(string $data, QrCodeStyle $style, QrCodeFormat $format, int $size): ResultInterface
    {
        $matrix = $this->matrixBuilder->build($data, $size, $style);

        return match ($format) {
            QrCodeFormat::Svg => $this->svgRenderer->render($matrix, $style),
            QrCodeFormat::Png => $this->pngRenderer->render($matrix, $style),
        };
    }
}
