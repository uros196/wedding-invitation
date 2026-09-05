<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MemoryWallUploadAdapter
{
    /**
     * Prepare the storage path before the browser starts uploading bytes.
     */
    public function prepare(Wedding $wedding, MemoryWallUpload $upload): void;

    /**
     * Publish the validated object through this upload strategy.
     *
     * @param  array{size: int, mime_type: string|null}  $metadata
     */
    public function finalize(Wedding $wedding, MemoryWallUpload $upload, array $metadata): Media;
}
