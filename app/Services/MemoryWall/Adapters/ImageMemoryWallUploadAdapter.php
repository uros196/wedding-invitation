<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Adapters;

use App\Contracts\MemoryWallUploadAdapter;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final readonly class ImageMemoryWallUploadAdapter implements MemoryWallUploadAdapter
{
    /**
     * Images use the existing pending-object-to-Media Library flow.
     */
    public function prepare(Wedding $wedding, MemoryWallUpload $upload): void
    {
        // The Media row is created by Spatie after final validation.
    }

    /**
     * Copy the validated object through Spatie so its normal lifecycle runs.
     *
     * @param  array{size: int, mime_type: string|null}  $metadata
     */
    public function finalize(Wedding $wedding, MemoryWallUpload $upload, array $metadata): Media
    {
        $mediaDisk = (string) config('memory-wall.media_disk', 's3');
        $conversionDisk = (string) config('memory-wall.conversions_disk', 's3');

        return $wedding
            ->addMediaFromDisk($upload->object_path, $mediaDisk)
            ->usingName(pathinfo($upload->original_name, PATHINFO_FILENAME))
            ->usingFileName(basename($upload->object_path))
            ->setFileSize($metadata['size'])
            ->storingConversionsOnDisk($conversionDisk)
            ->withProperties([
                'mime_type' => $metadata['mime_type'] ?: $upload->mime_type,
            ])
            ->toMediaCollection('MemoryWall', $mediaDisk);
    }
}
