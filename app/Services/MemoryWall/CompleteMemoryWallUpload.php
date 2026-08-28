<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Contracts\MemoryWallMultipartStorage;
use App\Enums\MemoryWallUploadStatus;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\Upload\Authorizer;
use App\Services\MemoryWall\Upload\Cleanup;
use App\Services\MemoryWall\Upload\PartValidator;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Verifies and publishes a multipart upload after object storage assembles it.
 *
 * Media remains hidden from the public gallery until both the received parts
 * and the final object's metadata match the trusted initialization request.
 */
final readonly class CompleteMemoryWallUpload
{
    /**
     * Create the upload completion action.
     */
    public function __construct(
        private Authorizer $authorizer,
        private PartValidator $partValidator,
        private Cleanup $cleanup,
        private MemoryWallMultipartStorage $storage,
    ) {}

    /**
     * Validate the remote upload and publish its Media Library record.
     */
    public function handle(Wedding $wedding, MemoryWallUpload $upload, string $uploadToken): Media
    {
        $this->authorizer->authorize($wedding, $upload, $uploadToken);

        if ($upload->status->isCompleted()) {
            return $upload->media()->firstOrFail();
        }

        $this->completeMultipartUpload($upload);

        // Do not expose the Media row until the assembled object passes the
        // final size and MIME checks.
        $metadata = $this->storage->objectMetadata($upload->object_path);

        if (! $this->isSizeExpected($metadata['size'], $upload)) {
            $this->failUpload($upload, __('wedding.memory_wall.validation.upload_size_mismatch'));
        }

        if (! $this->isMetaTypeAllowed($metadata['mime_type'], $upload)) {
            $this->failUpload($upload, __('wedding.memory_wall.validation.file_type'));
        }

        $media = $this->addMedia($wedding, $upload, $metadata);

        $upload->update([
            'media_id' => $media->getKey(),
            'status' => MemoryWallUploadStatus::Completed,
            'completed_at' => now(),
            'error_message' => null,
        ]);

        return $media;
    }

    /**
     * Move the validated object into the Wedding-owned Media Library collection.
     *
     * @param  array{size: int, mime_type: string|null}  $metadata
     */
    protected function addMedia(Wedding $wedding, MemoryWallUpload $upload, array $metadata): Media
    {
        $mediaDisk = (string) config('memory-wall.media_disk', 's3');
        $conversationDisk = (string) config('memory-wall.conversions_disk', 's3');

        return $wedding
            ->addMediaFromDisk($upload->object_path, $mediaDisk)
            ->usingName(pathinfo($upload->original_name, PATHINFO_FILENAME))
            ->usingFileName(basename($upload->object_path))
            ->setFileSize($metadata['size'])
            ->storingConversionsOnDisk($conversationDisk)
            ->withProperties([
                'mime_type' => $metadata['mime_type'] ?: $upload->mime_type,
            ])
            ->toMediaCollection('MemoryWall', $mediaDisk);
    }

    /**
     * Finalize a multipart upload by completing the process through the object storage provider.
     *
     * Ensures that all parts uploaded during the multipart upload process are confirmed and
     * assembled into the final object in storage.
     */
    protected function completeMultipartUpload(MemoryWallUpload $upload): void
    {
        // Object storage is authoritative; browser progress alone cannot prove
        // that every requested part reached the provider.
        $parts = $this->getParts($upload);

        $this->storage->completeMultipartUpload(
            $upload->object_path,
            (string) $upload->multipart_upload_id,
            $parts,
        );
    }

    /**
     * Retrieve and validate the parts of a multipart upload.
     */
    protected function getParts(MemoryWallUpload $upload): array
    {
        $partsList = $this->storage->listParts(
            $upload->object_path,
            (string) $upload->multipart_upload_id,
        );

        return $this->partValidator->validate($upload, $partsList);
    }

    /**
     * Determine if the given size matches the expected size of the upload.
     */
    protected function isSizeExpected(int $size, MemoryWallUpload $upload): bool
    {
        return $size === $upload->expected_size;
    }

    /**
     * Determine if the provided meta-type is allowed for the given upload.
     */
    protected function isMetaTypeAllowed(?string $metaType, MemoryWallUpload $upload): bool
    {
        $allowedMimeTypes = config("memory-wall.extension_mime_types.{$upload->extension}", []);

        return in_array($metaType, $allowedMimeTypes, true);
    }

    /**
     * Retain a failed session for operational visibility while removing the
     * object that did not pass final validation.
     */
    private function failUpload(MemoryWallUpload $upload, string $message): never
    {
        $this->cleanup->markAsFailed($upload, $message);

        throw ValidationException::withMessages([
            'file' => $message,
        ]);
    }
}
