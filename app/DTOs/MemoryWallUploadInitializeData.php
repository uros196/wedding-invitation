<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Http\Requests\MemoryWall\UploadRequest;

/**
 * Contains the validated metadata required to initialize one memory wall upload.
 */
final readonly class MemoryWallUploadInitializeData
{
    public function __construct(
        public string $clientUploadId,
        public string $uploadToken,
        public string $originalName,
        public int $size,
        public string $mimeType,
    ) {}

    /**
     * Create upload initialization data from a validated request.
     */
    public static function fromRequest(UploadRequest $request): self
    {
        $safe = $request->safe();

        return new self(
            clientUploadId: (string) $safe->string('client_upload_id'),
            uploadToken: (string) $safe->string('upload_token'),
            originalName: (string) $safe->string('file_name'),
            size: $safe->integer('size'),
            mimeType: (string) $safe->string('mime_type'),
        );
    }
}
