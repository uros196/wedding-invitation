<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Upload;

use App\Contracts\MemoryWallMultipartStorage;
use App\Enums\MemoryWallUploadStatus;
use App\Models\MemoryWallUpload;
use Throwable;

/**
 * Keeps local upload state and remote object-storage state consistent during
 * retries, cancellations, and final-validation failures.
 */
final readonly class Cleanup
{
    /**
     * Create the cleanup coordinator.
     */
    public function __construct(private MemoryWallMultipartStorage $storage) {}

    /**
     * Reset a failed session so initialization can safely retry it.
     */
    public function resetFailedUpload(MemoryWallUpload $upload): void
    {
        if (filled($upload->multipart_upload_id)) {
            try {
                $this->storage->abortMultipartUpload(
                    $upload->object_path,
                    (string) $upload->multipart_upload_id,
                );
            } catch (Throwable) {
                // The provider may have expired the old multipart session.
            }
        }

        try {
            $this->storage->deleteObject($upload->object_path);
        } catch (Throwable) {
            // A missing object is harmless because the next session reuses the path.
        }

        $upload->update([
            'status' => MemoryWallUploadStatus::Uploading,
            'multipart_upload_id' => null,
            'error_message' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Mark final validation failure while removing the untrusted object.
     */
    public function markAsFailed(MemoryWallUpload $upload, string $message): void
    {
        try {
            $this->storage->deleteObject($upload->object_path);
        } catch (Throwable) {
            // Keep the failed record for later operational cleanup.
        }

        $upload->update([
            'status' => MemoryWallUploadStatus::Failed,
            'error_message' => $message,
        ]);
    }

    /**
     * Abort an unfinished remote session and remove its database records.
     */
    public function cancel(MemoryWallUpload $upload): void
    {
        if (filled($upload->multipart_upload_id)) {
            $this->storage->abortMultipartUpload(
                $upload->object_path,
                (string) $upload->multipart_upload_id,
            );
        }

        $upload->delete();
    }
}
