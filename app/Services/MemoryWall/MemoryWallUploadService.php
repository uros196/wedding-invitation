<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\DTOs\MemoryWallUploadInitializeData;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Backwards-compatible facade for the split memory wall upload actions.
 *
 * New HTTP code injects the individual actions directly. This small delegator
 * keeps the existing service API available to callers that still use it while
 * ensuring that business logic lives in focused classes.
 */
final readonly class MemoryWallUploadService
{
    /**
     * Create the facade from the four upload use cases.
     */
    public function __construct(
        private InitializeMemoryWallUpload $initializeUpload,
        private GetMemoryWallUploadPartUrls $getPartUrls,
        private CompleteMemoryWallUpload $completeUpload,
        private CancelMemoryWallUpload $cancelUpload,
    ) {}

    /**
     * Delegate upload initialization to its action.
     */
    public function initialize(Wedding $wedding, MemoryWallUploadInitializeData $data): MemoryWallUpload
    {
        return $this->initializeUpload->handle($wedding, $data);
    }

    /**
     * Delegate part URL generation to its action.
     *
     * @return array<int, array{part_number: int, url: string}>
     */
    public function getPartUrls(Wedding $wedding, MemoryWallUpload $upload, string $uploadToken): array
    {
        return $this->getPartUrls->handle($wedding, $upload, $uploadToken);
    }

    /**
     * Delegate multipart completion to its action.
     */
    public function complete(Wedding $wedding, MemoryWallUpload $upload, string $uploadToken): Media
    {
        return $this->completeUpload->handle($wedding, $upload, $uploadToken);
    }

    /**
     * Delegate upload cancellation to its action.
     */
    public function cancel(Wedding $wedding, MemoryWallUpload $upload, string $uploadToken): void
    {
        $this->cancelUpload->handle($wedding, $upload, $uploadToken);
    }
}
