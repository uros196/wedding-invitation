<?php

declare(strict_types=1);

namespace App\Support\MemoryWall;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MemoryWallMediaType
{
    /**
     * The placeholder displayed for video uploads where an image preview is not available.
     */
    private const string VIDEO_PLACEHOLDER = 'images/video-placeholder.svg';

    /**
     * Generate a preview URL or placeholder for the given media.
     */
    public static function preview(?Media $media): ?string
    {
        if (is_null($media)) {
            return null;
        }

        return self::isVideo($media->mime_type)
            ? self::videoPlaceholderUrl()
            : self::previewUrl($media);
    }

    /**
     * Determine whether the given MIME type describes a video.
     */
    public static function isVideo(?string $mimeType): bool
    {
        return Str::startsWith($mimeType ?? '', 'video/');
    }

    /**
     * Resolve the generated preview URL without falling back to the original file.
     */
    public static function previewUrl(?Media $media): ?string
    {
        if ($media === null || ! $media->hasGeneratedConversion('preview')) {
            return null;
        }

        return $media->getUrl('preview');
    }

    /**
     * Resolve the public placeholder image URL for video uploads.
     */
    public static function videoPlaceholderUrl(): string
    {
        return asset(self::VIDEO_PLACEHOLDER);
    }
}
