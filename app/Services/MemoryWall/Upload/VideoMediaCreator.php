<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Upload;

use App\Enums\MediaStatus;
use App\Models\Media;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Reserves a Wedding-owned Media row so a video can upload directly to its path.
 */
final class VideoMediaCreator
{
    /**
     * Create or reuse the hidden Media row for a video upload.
     */
    public function create(Wedding $wedding, MemoryWallUpload $upload): Media
    {
        $media = $this->findExisting($wedding, $upload);

        if ($media !== null) {
            $media->markAsPending();
            $this->syncObjectPath($upload, $media);

            return $media;
        }

        return DB::transaction(function () use ($wedding, $upload): Media {
            $media = new Media([
                'collection_name' => Wedding::MEMORY_WALL_COLLECTION,
                'name' => pathinfo($upload->original_name, PATHINFO_FILENAME),
                'file_name' => "{$upload->uuid}.{$upload->extension}",
                'mime_type' => $upload->mime_type,
                'disk' => (string) config('memory-wall.media_disk', 's3'),
                'conversions_disk' => (string) config('memory-wall.conversions_disk', 's3'),
                'size' => $upload->expected_size,
                'manipulations' => [],
                'custom_properties' => [],
                'generated_conversions' => [],
                'responsive_images' => [],
                'status' => MediaStatus::Pending,
            ]);
            $media->model()->associate($wedding);
            $media->save();

            $this->syncObjectPath($upload, $media);

            return $media;
        });
    }

    /**
     * Find the reserved Media row, including rows hidden by the default scope.
     */
    public function findForUpload(Wedding $wedding, MemoryWallUpload $upload): Media
    {
        if ($upload->media_id === null) {
            throw (new ModelNotFoundException)->setModel(Media::class);
        }

        $media = Media::withoutReady()->findOrFail($upload->media_id);
        $this->ensureMediaBelongsToUpload($media, $wedding);

        return $media;
    }

    /**
     * Find an existing row while allowing both ready and pending states.
     */
    private function findExisting(Wedding $wedding, MemoryWallUpload $upload): ?Media
    {
        if ($upload->media_id === null) {
            return null;
        }

        $media = Media::withoutReady()->find($upload->media_id);

        if ($media !== null) {
            $this->ensureMediaBelongsToUpload($media, $wedding);
        }

        return $media;
    }

    /**
     * Keep the multipart object key synchronized with Spatie's generated path.
     */
    private function syncObjectPath(MemoryWallUpload $upload, Media $media): void
    {
        $upload->update([
            'media_id' => $media->getKey(),
            'object_path' => $media->getPathRelativeToRoot(),
        ]);
    }

    /**
     * Prevent a stale or tampered upload from publishing another wedding's media.
     */
    private function ensureMediaBelongsToUpload(Media $media, Wedding $wedding): void
    {
        if (
            $media->model_type !== $wedding->getMorphClass()
            || (int) $media->model_id !== (int) $wedding->getKey()
            || $media->collection_name !== Wedding::MEMORY_WALL_COLLECTION
        ) {
            throw new ModelNotFoundException;
        }
    }
}
