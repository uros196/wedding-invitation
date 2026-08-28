<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Contracts\MemoryWallMultipartStorage;
use Aws\S3\S3Client;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * S3-compatible implementation of the memory wall multipart storage contract.
 *
 * Laravel coordinates the session through this adapter, while the browser
 * uploads each part directly to the configured bucket with a presigned URL.
 */
final class S3MultipartUploadStorage implements MemoryWallMultipartStorage
{
    private readonly S3Client $client;

    private readonly string $bucket;

    private readonly int $presignedUrlMinutes;

    /**
     * Resolve the configured Memory Wall media disk and reuse its SDK client.
     *
     * An optional client is accepted so the storage boundary can be exercised
     * with a controlled SDK client in tests.
     */
    public function __construct(?S3Client $client = null)
    {
        $diskName = (string) config('memory-wall.media_disk', 's3');
        $disk = Storage::disk($diskName);

        if (! $disk instanceof AwsS3V3Adapter) {
            throw new InvalidArgumentException(
                "Memory Wall media disk [{$diskName}] must use the S3 driver."
            );
        }

        $this->client = $client ?? $disk->getClient();
        $this->bucket = (string) ($disk->getConfig()['bucket'] ?? '');
        $this->presignedUrlMinutes = (int) config('memory-wall.presigned_url_minutes', 30);
    }

    /**
     * Open a multipart upload and preserve the intended content type on S3.
     */
    public function createMultipartUpload(string $path, string $mimeType): string
    {
        $result = $this->client->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'ContentType' => $mimeType,
        ]);

        return (string) $result['UploadId'];
    }

    /**
     * Generate one presigned `UploadPart` request for each expected part.
     *
     * URLs are intentionally short-lived and scoped to one object, session,
     * and part number, so the browser never receives general bucket access.
     *
     * @return array<int, array{part_number: int, url: string}>
     */
    public function temporaryPartUrls(string $path, string $uploadId, int $totalParts): array
    {
        $urls = [];

        for ($partNumber = 1; $partNumber <= $totalParts; $partNumber++) {
            $command = $this->client->getCommand('UploadPart', [
                'Bucket' => $this->bucket,
                'Key' => $path,
                'UploadId' => $uploadId,
                'PartNumber' => $partNumber,
            ]);

            $request = $this->client->createPresignedRequest(
                $command,
                now()->addMinutes($this->presignedUrlMinutes),
            );

            $urls[] = [
                'part_number' => $partNumber,
                'url' => (string) $request->getUri(),
            ];
        }

        return $urls;
    }

    /**
     * Ask S3 to assemble the already uploaded parts into the final object.
     *
     * @param  array<int, array{part_number: int, etag: string}>  $parts
     */
    public function completeMultipartUpload(string $path, string $uploadId, array $parts): void
    {
        $this->client->completeMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => array_map(
                    static fn (array $part): array => [
                        'PartNumber' => $part['part_number'],
                        'ETag' => $part['etag'],
                    ],
                    $parts,
                ),
            ],
        ]);
    }

    /**
     * List the parts currently stored by S3, including every paginated result.
     *
     * @return array<int, array{part_number: int, etag: string, size: int}>
     */
    public function listParts(string $path, string $uploadId): array
    {
        $parts = [];
        $marker = null;

        // Multipart uploads can contain more parts than one S3 response can
        // return, so continue from the provider's marker until the list ends.
        do {
            $result = $this->client->listParts(array_filter([
                'Bucket' => $this->bucket,
                'Key' => $path,
                'UploadId' => $uploadId,
                'PartNumberMarker' => $marker,
            ], static fn (mixed $value): bool => $value !== null));

            foreach ($result['Parts'] ?? [] as $part) {
                $parts[] = [
                    'part_number' => (int) $part['PartNumber'],
                    'etag' => (string) $part['ETag'],
                    'size' => (int) $part['Size'],
                ];
            }

            $marker = ($result['IsTruncated'] ?? false)
                ? (string) $result['NextPartNumberMarker']
                : null;
        } while ($marker !== null);

        return $parts;
    }

    /**
     * Read the final size and sniff the content instead of trusting request
     * metadata supplied by the browser.
     *
     * Only the first mebibyte is downloaded for MIME detection; this keeps the
     * completion request bounded even when the file itself is close to 1 GiB.
     *
     * @return array{size: int, mime_type: string|null}
     */
    public function objectMetadata(string $path): array
    {
        $result = $this->client->headObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
        ]);
        // A small byte range is enough for finfo and avoids downloading the
        // complete object back through the Laravel application server.
        $sample = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'Range' => 'bytes=0-1048575',
        ]);
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer((string) $sample['Body']) ?: null;

        return [
            'size' => (int) ($result['ContentLength'] ?? 0),
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Abort the remote session and release all parts that were uploaded so far.
     */
    public function abortMultipartUpload(string $path, string $uploadId): void
    {
        $this->client->abortMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'UploadId' => $uploadId,
        ]);
    }

    /**
     * Delete an assembled object that failed final validation.
     */
    public function deleteObject(string $path): void
    {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
        ]);
    }
}
