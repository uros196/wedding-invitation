<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Contracts\MemoryWallArchiveStorage;
use Aws\S3\S3Client;
use DateTimeInterface;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * Stores an archive as a multipart object so its size is not limited by local
 * worker disk space or PHP memory.
 */
final readonly class S3MemoryWallArchiveStorage implements MemoryWallArchiveStorage
{
    private S3Client $client;

    private string $bucket;

    private string $diskName;

    /**
     * Resolve the configured archive disk and reuse its S3 client.
     */
    public function __construct(?S3Client $client = null)
    {
        $this->diskName = (string) config(
            'memory-wall.archive_disk',
            config('memory-wall.media_disk', 's3'),
        );
        $disk = Storage::disk($this->diskName);

        if (! $disk instanceof AwsS3V3Adapter) {
            throw new InvalidArgumentException(
                "Memory Wall archive disk [{$this->diskName}] must use the S3 driver.",
            );
        }

        $this->client = $client ?? $disk->getClient();
        $this->bucket = (string) ($disk->getConfig()['bucket'] ?? '');
    }

    /**
     * Start a ZIP multipart upload with download headers attached to the object.
     */
    public function createMultipartUpload(string $path, string $filename): string
    {
        $result = $this->client->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'ContentType' => 'application/zip',
            'ContentDisposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $filename,
            ),
        ]);

        return (string) $result['UploadId'];
    }

    /**
     * Upload one buffered ZIP part and return its ETag.
     */
    public function uploadPart(string $path, string $uploadId, int $partNumber, string $contents): string
    {
        $result = $this->client->uploadPart([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
            'Body' => $contents,
            'ContentLength' => strlen($contents),
        ]);

        return (string) $result['ETag'];
    }

    /**
     * Ask S3 to assemble the uploaded parts into one archive object.
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
     * Remove a failed multipart upload and all parts associated with it.
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
     * Create a short-lived URL so the browser downloads directly from S3.
     */
    public function temporaryUrl(string $path, DateTimeInterface $expiration, string $filename): string
    {
        return Storage::disk($this->diskName)->temporaryUrl(
            $path,
            $expiration,
            [
                'ResponseContentDisposition' => HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    $filename,
                ),
                'ResponseContentType' => 'application/zip',
            ],
        );
    }

    /**
     * Delete a completed archive after its retention window expires.
     */
    public function deleteObject(string $path): void
    {
        Storage::disk($this->diskName)->delete($path);
    }
}
