<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Defines the object-storage operations required by the memory wall upload flow.
 *
 * Keeping this contract separate from the upload service allows the application
 * to use any S3-compatible storage implementation without coupling the domain
 * workflow to a concrete SDK client.
 */
interface MemoryWallMultipartStorage
{
    /**
     * Start a multipart session for the object that will receive the file.
     */
    public function createMultipartUpload(string $path, string $mimeType): string;

    /**
     * Create one short-lived upload URL for every expected file part.
     *
     * The browser uses these URLs to send bytes directly to object storage, so
     * the large file does not have to pass through the Laravel request process.
     *
     * @return array<int, array{part_number: int, url: string}>
     */
    public function temporaryPartUrls(string $path, string $uploadId, int $totalParts): array;

    /**
     * Commit the uploaded parts into one completed object.
     *
     * @param  array<int, array{part_number: int, etag: string}>  $parts
     */
    public function completeMultipartUpload(string $path, string $uploadId, array $parts): void;

    /**
     * Read the parts that object storage actually received.
     *
     * @return array<int, array{part_number: int, etag: string, size: int}>
     */
    public function listParts(string $path, string $uploadId): array;

    /**
     * Read the final object's size and detect its MIME type after assembly.
     *
     * @return array{size: int, mime_type: string|null}
     */
    public function objectMetadata(string $path): array;

    /**
     * Discard an unfinished multipart session and all of its uploaded parts.
     */
    public function abortMultipartUpload(string $path, string $uploadId): void;

    /**
     * Remove a completed object when final validation fails.
     */
    public function deleteObject(string $path): void;
}
