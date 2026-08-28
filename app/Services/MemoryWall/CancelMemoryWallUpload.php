<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\Upload\Authorizer;
use App\Services\MemoryWall\Upload\Cleanup;
use Illuminate\Validation\ValidationException;

/**
 * Cancels an unfinished multipart session and removes its control-plane record.
 */
final readonly class CancelMemoryWallUpload
{
    /**
     * Create the upload cancellation action.
     */
    public function __construct(
        private Authorizer $authorizer,
        private Cleanup $cleanup,
    ) {}

    /**
     * Abort a remote state and delete the local control-plane records.
     */
    public function handle(Wedding $wedding, MemoryWallUpload $upload, string $uploadToken): void
    {
        $this->authorizer->authorize($wedding, $upload, $uploadToken);

        if ($upload->status->isCompleted()) {
            throw ValidationException::withMessages([
                'upload' => __('wedding.memory_wall.validation.completed_upload_cannot_be_cancelled'),
            ]);
        }

        $this->cleanup->cancel($upload);
    }
}
