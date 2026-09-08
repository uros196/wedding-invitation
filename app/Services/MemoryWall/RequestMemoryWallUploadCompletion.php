<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Jobs\CompleteMemoryWallUploadJob;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\Upload\Authorizer;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Authorizes completion requests and queues unfinished uploads for processing.
 */
final readonly class RequestMemoryWallUploadCompletion
{
    /**
     * Create the upload completion request action.
     */
    public function __construct(
        private Authorizer $authorizer,
    ) {}

    /**
     * Return completed media, or null while completion is pending.
     */
    public function handle(Wedding $wedding, MemoryWallUpload $upload, string $uploadToken): ?Media
    {
        $this->authorizer->authorize($wedding, $upload, $uploadToken);

        if ($upload->status->isFailed()) {
            throw ValidationException::withMessages([
                'file' => $upload->error_message ?? __('wedding.memory_wall.validation.processing_failed'),
            ]);
        }

        if ($upload->status->isCompleted()) {
            return $upload->media()->firstOrFail();
        }

        if ($upload->status->isUploading()) {
            CompleteMemoryWallUploadJob::dispatch($wedding, $upload);
        }

        return null;
    }
}
