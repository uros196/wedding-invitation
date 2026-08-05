<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder;

use Endroid\QrCode\Bacon\MatrixFactory;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\QrCode;

/**
 * Creates the QR matrix independently from the format-specific renderers.
 */
final class QrCodeMatrixBuilder
{
    /**
     * Build a QR matrix from the payload and a visual style.
     */
    public function build(string $data, int $size, QrCodeStyle $style): MatrixInterface
    {
        $qrCode = new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: $style->errorCorrectionLevel,
            size: $size,
            margin: $style->margin,
            roundBlockSizeMode: $style->roundBlockSizeMode,
            foregroundColor: $style->foregroundColor,
            backgroundColor: $style->backgroundColor,
        );

        return (new MatrixFactory)->create($qrCode);
    }
}
