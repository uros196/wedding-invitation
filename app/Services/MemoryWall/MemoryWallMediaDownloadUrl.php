<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Contracts\MemoryWallMediaUrl;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * Creates a short-lived direct URL for one original media object.
 */
final class MemoryWallMediaDownloadUrl implements MemoryWallMediaUrl
{
    /**
     * Create a direct storage URL so Laravel does not proxy large media files.
     */
    public function make(Media $media, string $filename): string
    {
        return Storage::disk($media->disk)->temporaryUrl(
            $media->getPathRelativeToRoot(),
            now()->addMinutes((int) config('memory-wall.download_url_minutes', 60)),
            [
                'ResponseContentDisposition' => HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    $filename,
                ),
                'ResponseContentType' => $media->mime_type,
            ],
        );
    }
}
