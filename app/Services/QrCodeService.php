<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QrCodeFormat;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;

class QrCodeService
{
    private const int MARGIN = 16;

    private const int FOREGROUND_RED = 74;

    private const int FOREGROUND_GREEN = 52;

    private const int FOREGROUND_BLUE = 46;

    public function generate(string $data, QrCodeFormat $format, int $size): ResultInterface
    {
        return (new Builder(
            writer: $this->writerFor($format),
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: self::MARGIN,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(
                self::FOREGROUND_RED,
                self::FOREGROUND_GREEN,
                self::FOREGROUND_BLUE,
            ),
            backgroundColor: new Color(255, 255, 255),
        ))->build();
    }

    private function writerFor(QrCodeFormat $format): WriterInterface
    {
        return match ($format) {
            QrCodeFormat::Svg => new SvgWriter,
            QrCodeFormat::Png => new PngWriter,
        };
    }
}
