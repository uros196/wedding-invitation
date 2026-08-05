<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QrCodeFormat;
use App\Support\QrCodeBuilder\QrCodeBuilder;
use App\Support\QrCodeBuilder\QrCodeFactory;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * Provides the application-facing API for generating invitation QR codes.
 */
final readonly class QrCodeService
{
    public function __construct(
        private QrCodeBuilder $qrCodeBuilder,
        private QrCodeFactory $factory,
    ) {}

    /**
     * Generate a QR code result for the given payload.
     */
    public function generateForWedding(string $data, QrCodeFormat $format, int $size): ResultInterface
    {
        $style = $this->factory->makeForWedding();

        return $this->qrCodeBuilder->build($data, $style, $format, $size);
    }
}
