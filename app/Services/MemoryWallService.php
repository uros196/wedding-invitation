<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QrCodeFormat;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemoryWallService
{
    /**
     * Check if the memory wall is enabled for the given wedding.
     */
    public function isEnabled(Wedding $wedding): bool
    {
        return $wedding->has_memory_wall;
    }

    /**
     * Determine if the memory wall form is open for the given wedding.
     */
    public function isFormOpen(Wedding $wedding): bool
    {
        $openUntil = $wedding->memory_wall_open_until
            ?? $wedding->wedding_date->addDays(config('wedding.memory_wall.form_open_for'));

        return $wedding->has_memory_wall
            && $openUntil->isFuture();
    }

    /**
     * Upload files to the wedding memory wall.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public function uploadFiles(Wedding $wedding, array $files): void
    {
        foreach ($files as $file) {
            $wedding->addMedia($file)
                ->toMediaCollection('MemoryWall');
        }
    }

    /**
     * Retrieve a random collection of media files from the wedding's memory wall.
     */
    public function getRandomFiles(Wedding $wedding, int $limit = 10): Collection
    {
        return $wedding->media()
            ->where('collection_name', 'MemoryWall')
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /**
     * Generate a QR code for the wedding's memory wall.
     */
    public function generateQrCode(Wedding $wedding, int $size = 200): string
    {
        return $this->getQrCode($wedding, $size)->toHtml();
    }

    /**
     * Get the QR code instance for the wedding's memory wall.
     */
    public function getQrCode(Wedding $wedding, int $size = 200, ?QrCodeFormat $format = null): HtmlString|string
    {
        return QrCode::format(($format ?? QrCodeFormat::default())->value)
            ->size($size)
            ->generate($wedding->memory_wall_url);
    }

    /**
     * Stream the QR code file for the specified wedding and download option.
     */
    public function downloadQrCode(Wedding $wedding, QrCodeFormat $option, int $size = 200): StreamedResponse
    {
        $qrCode = $this->getQrCode($wedding, $size, $option);

        return response()->streamDownload(
            fn () => print($qrCode),
            "qr-code-{$wedding->uuid}.{$option->extension()}",
            ['Content-Type' => $option->contentType()]
        );
    }
}
