<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Upload;

use App\DTOs\MemoryWallUploadInitializeData;
use App\Enums\MemoryWallUploadStatus;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates the control-plane record for an upload.
 */
final readonly class RecordCreator
{
    /**
     * Create the upload record before bytes are sent to object storage.
     */
    public function create(Wedding $wedding, MemoryWallUploadInitializeData $data): MemoryWallUpload
    {
        return DB::transaction(function () use ($wedding, $data): MemoryWallUpload {
            $extension = $this->extension($data->originalName);
            $uploadUuid = Str::uuid()->toString();

            return $wedding->memoryWallUploads()->create([
                'uuid' => $uploadUuid,
                'client_upload_id' => $data->clientUploadId,
                'upload_token_hash' => hash('sha256', $data->uploadToken),
                'object_path' => $this->objectPath($wedding, $uploadUuid, $extension),
                'original_name' => $data->originalName,
                'extension' => $extension,
                'mime_type' => $data->mimeType,
                'expected_size' => $data->size,
                'part_size' => $this->partSize(),
                'total_parts' => $this->totalParts($data->size),
                'status' => MemoryWallUploadStatus::Uploading,
            ]);
        });
    }

    /**
     * Extract and return the file extension from the given filename.
     */
    protected function extension(string $filename): string
    {
        return Str::lower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Calculates the total number of parts based on the given size and the part size.
     */
    protected function totalParts(int $size): int
    {
        return (int) ceil($size / $this->partSize());
    }

    /**
     * Retrieves the configured part size from the memory-wall configuration.
     */
    protected function partSize(): int
    {
        return (int) config('memory-wall.part_size');
    }

    /**
     * Resolve a private temporary object path for the multipart upload.
     */
    private function objectPath(Wedding $wedding, string $uploadUuid, string $extension): string
    {
        return "memory-wall/pending/{$wedding->uuid}/{$uploadUuid}.{$extension}";
    }
}
