<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder;

use App\Enums\QrCodeFormat;
use App\Support\QrCodeBuilder\Data\QrCodeLogo;

/**
 * Loads only the static, format-compatible logo assets configured for QR codes.
 */
final class QrCodeLogoLoader
{
    /**
     * Load and size the logo required by a QR output format.
     */
    public function load(QrCodeStyle $style, QrCodeFormat $format, int $qrSize): QrCodeLogo
    {
        $logoPath = $style->logoPathFor($format);

        if (! is_file($logoPath) || ! is_readable($logoPath)) {
            throw new \RuntimeException(sprintf('Could not read QR code logo at "%s".', $logoPath));
        }

        $data = file_get_contents($logoPath);

        if (! is_string($data)) {
            throw new \RuntimeException(sprintf('Could not read QR code logo at "%s".', $logoPath));
        }

        $mimeType = $this->mimeType($logoPath);
        $this->ensureFormatMatches($mimeType, $format, $logoPath);
        $logoSize = $style->logoSize($qrSize);
        $image = null;

        if ($format === QrCodeFormat::Png) {
            $image = $this->loadRasterImage($data, $logoPath);
        }

        return new QrCodeLogo(
            data: $data,
            mimeType: $mimeType,
            width: $logoSize,
            height: $logoSize,
            image: $image,
        );
    }

    /**
     * Resolve a stable MIME type for the local logo asset.
     */
    private function mimeType(string $path): string
    {
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if (! is_string($mimeType)) {
            throw new \RuntimeException(sprintf('Could not determine QR code logo type at "%s".', $path));
        }

        return match ($mimeType) {
            'image/svg', 'text/xml', 'application/xml' => 'image/svg+xml',
            default => $mimeType,
        };
    }

    /**
     * Reject a logo asset that cannot be embedded by the selected renderer.
     */
    private function ensureFormatMatches(string $mimeType, QrCodeFormat $format, string $path): void
    {
        $isSupported = $format->isMimeType($mimeType);

        if (! $isSupported) {
            throw new \RuntimeException(sprintf(
                'QR code logo at "%s" is not a valid %s asset.',
                $path,
                $format->getLabel(),
            ));
        }
    }

    /**
     * Decode the raster logo needed by the GD renderer.
     */
    private function loadRasterImage(string $data, string $path): \GdImage
    {
        if (! function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('Unable to load QR code logo: the GD extension is not enabled.');
        }

        $image = imagecreatefromstring($data);

        if (! $image instanceof \GdImage) {
            throw new \RuntimeException(sprintf('Could not parse QR code logo at "%s".', $path));
        }

        return $image;
    }
}
