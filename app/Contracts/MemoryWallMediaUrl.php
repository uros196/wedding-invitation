<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Media;

/**
 * Creates authorized temporary URLs for original memory wall media.
 */
interface MemoryWallMediaUrl
{
    /**
     * Build a browser download URL that preserves the requested filename.
     */
    public function make(Media $media, string $filename): string;
}
