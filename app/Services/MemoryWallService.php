<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MemoryWallUploadStatus;
use App\Enums\QrCodeFormat;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class MemoryWallService
{
    public function __construct(private QrCodeService $qrCodeService) {}

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
        $weddingDate = $wedding->wedding_date;

        if (blank($weddingDate)) {
            return false;
        }

        $openUntil = $wedding->memory_wall_open_until
            ?? $weddingDate->addDays(config('wedding.invitation.memory_wall.form_open_for'));

        return $wedding->has_memory_wall
            && ($weddingDate->isToday() || $weddingDate->isPast())
            && $openUntil->isFuture();
    }

    /**
     * Retrieve random media that is safe to show in the public preview.
     *
     * Legacy media without an upload-session row remains visible, while media
     * created by the multipart flow is shown only after its session completes.
     */
    public function getRandomFiles(Wedding $wedding, int $limit = 12): Collection
    {
        $uploadTable = (new MemoryWallUpload)->getTable();
        $mediaTable = $wedding->media()->getModel()->getTable();

        return $wedding->media()
            ->where('collection_name', 'MemoryWall')
            ->where(function (Builder $query) use ($uploadTable, $mediaTable): void {
                $query
                    ->whereNotExists(function (QueryBuilder $query) use ($uploadTable, $mediaTable): void {
                        $query->selectRaw('1')
                            ->from($uploadTable)
                            ->whereColumn("{$uploadTable}.media_id", "{$mediaTable}.id");
                    })
                    ->orWhereExists(function (QueryBuilder $query) use ($uploadTable, $mediaTable): void {
                        $query->selectRaw('1')
                            ->from($uploadTable)
                            ->whereColumn("{$uploadTable}.media_id", "{$mediaTable}.id")
                            ->where("{$uploadTable}.status", MemoryWallUploadStatus::Completed->value);
                    });
            })
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /**
     * Generate a QR code for the wedding's memory wall.
     */
    public function generateQrCode(Wedding $wedding, int $size = 200): string
    {
        $qrCode = $this->getQrCode($wedding, $size);

        return $qrCode instanceof HtmlString ? $qrCode->toHtml() : $qrCode;
    }

    /**
     * Get the QR code instance for the wedding's memory wall.
     */
    public function getQrCode(Wedding $wedding, int $size = 200, ?QrCodeFormat $format = null): HtmlString|string
    {
        $format ??= QrCodeFormat::default();
        $result = $this->qrCodeService->generateForWedding($wedding->memory_wall_url, $format, $size);

        return $format === QrCodeFormat::Svg
            ? new HtmlString($result->getString())
            : $result->getString();
    }

    /**
     * Stream the QR code file for the specified wedding and download option.
     */
    public function downloadQrCode(Wedding $wedding, QrCodeFormat $option, int $size = 200): StreamedResponse
    {
        $qrCode = $this->getQrCode($wedding, $size, $option);
        $weddingTitle = Str::slug($wedding->wedding_title);

        return response()->streamDownload(
            fn () => print ($qrCode),
            "qr-code-{$weddingTitle}.{$option->extension()}",
            ['Content-Type' => $option->contentType()]
        );
    }
}
