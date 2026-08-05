<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

/**
 * Provides the QR code appearance for each supported invitation panel type.
 */
final class QrCodeFactory
{
    /**
     * Create the QR code style used by wedding invitations.
     */
    public function makeForWedding(): QrCodeStyle
    {
        return new QrCodeStyle(
            foregroundColor: new Color(74, 52, 46),
            backgroundColor: new Color(255, 255, 255),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            roundBlockSizeMode: RoundBlockSizeMode::None,
            margin: 10,
            logoPath: public_path('favicon.svg'),
            rasterLogoPath: public_path('favicon-32x32.png'),
            dotScale: 0.78,
            finderRadius: 1.2,
            logoSizeRatio: 0.18,
            logoMarginRatio: 0.04,
            minimumLogoSize: 14,
            maximumLogoSize: 48,
            minimumLogoMargin: 4,
            maximumLogoMargin: 10,
        );
    }
}
