<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Adapters;

use App\Contracts\MemoryWallUploadAdapter;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\Upload\VideoMediaCreator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final readonly class VideoMemoryWallUploadAdapter implements MemoryWallUploadAdapter
{
    /**
     * Create the adapter for direct-to-final-path video uploads.
     */
    public function __construct(private VideoMediaCreator $mediaCreator) {}

    /**
     * Reserve the final Spatie path before the browser uploads any bytes.
     */
    public function prepare(Wedding $wedding, MemoryWallUpload $upload): void
    {
        $this->mediaCreator->create($wedding, $upload);
    }

    /**
     * Publish the already assembled object by making its reserved Media row ready.
     *
     * @param  array{size: int, mime_type: string|null}  $metadata
     */
    public function finalize(Wedding $wedding, MemoryWallUpload $upload, array $metadata): Media
    {
        $media = $this->mediaCreator->findForUpload($wedding, $upload);
        $media->forceFill([
            'mime_type' => $metadata['mime_type'] ?: $upload->mime_type,
            'size' => $metadata['size'],
        ]);
        $media->markAsReady();

        return $media;
    }
}
