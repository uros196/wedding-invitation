<?php

declare(strict_types=1);

namespace App\Contracts;

use DateTimeInterface;

/**
 * Defines the multipart operations used while creating a memory wall archive.
 */
interface MemoryWallArchiveStorage
{
    /**
     * Start an S3 multipart upload and return its provider-specific ID.
     */
    public function createMultipartUpload(string $path, string $filename): string;

    /**
     * Upload one archive part and return its ETag for completion.
     */
    public function uploadPart(string $path, string $uploadId, int $partNumber, string $contents): string;

    /**
     * @param  array<int, array{part_number: int, etag: string}>  $parts
     */
    public function completeMultipartUpload(string $path, string $uploadId, array $parts): void;

    /**
     * Cancel an unfinished multipart upload so its parts are not retained.
     */
    public function abortMultipartUpload(string $path, string $uploadId): void;

    /**
     * Create a temporary browser download URL for a completed archive.
     */
    public function temporaryUrl(string $path, DateTimeInterface $expiration, string $filename): string;

    /**
     * Remove an expired archive object from storage.
     */
    public function deleteObject(string $path): void;
}
