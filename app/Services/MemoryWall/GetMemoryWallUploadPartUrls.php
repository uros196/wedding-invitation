<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Contracts\MemoryWallMultipartStorage;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\Upload\Authorizer;

/**
 * Returns the short-lived object-storage URLs for an existing upload session.
 */
final readonly class GetMemoryWallUploadPartUrls
{
    /**
     * Create the part URL action.
     */
    public function __construct(
        private Authorizer $authorizer,
        private MemoryWallMultipartStorage $storage,
    ) {}

    /**
     * Generate one presigned URL for every expected multipart part.
     *
     * A completed session is already idempotently finished and therefore
     * returns no URLs.
     *
     * @return array<int, array{part_number: int, url: string}>
     */
    public function handle(Wedding $wedding, MemoryWallUpload $upload, string $uploadToken): array
    {
        $this->authorizer->authorize($wedding, $upload, $uploadToken);

        if ($upload->status->isCompleted()) {
            return [];
        }

        return $this->storage->temporaryPartUrls(
            $upload->object_path,
            (string) $upload->multipart_upload_id,
            $upload->total_parts,
        );
    }
}
