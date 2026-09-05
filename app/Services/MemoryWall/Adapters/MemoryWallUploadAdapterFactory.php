<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Adapters;

use App\Contracts\MemoryWallUploadAdapter;
use App\Models\MemoryWallUpload;
use Illuminate\Support\Str;

final readonly class MemoryWallUploadAdapterFactory
{
    public function __construct(
        private ImageMemoryWallUploadAdapter $imageAdapter,
        private VideoMemoryWallUploadAdapter $videoAdapter,
    ) {}

    /**
     * Select the upload strategy for the MIME type supplied by the browser.
     */
    public function forMimeType(string $mimeType): MemoryWallUploadAdapter
    {
        return Str::startsWith($mimeType, 'video/')
            ? $this->videoAdapter
            : $this->imageAdapter;
    }

    /**
     * Select the upload strategy for a persisted upload session.
     */
    public function forUpload(MemoryWallUpload $upload): MemoryWallUploadAdapter
    {
        return $this->forMimeType($upload->mime_type);
    }
}
