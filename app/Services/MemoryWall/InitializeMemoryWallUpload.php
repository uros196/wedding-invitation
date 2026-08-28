<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Contracts\MemoryWallMultipartStorage;
use App\DTOs\MemoryWallUploadInitializeData;
use App\Enums\MemoryWallUploadStatus;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\Upload\Authorizer;
use App\Services\MemoryWall\Upload\Cleanup;
use App\Services\MemoryWall\Upload\MetadataValidator;
use App\Services\MemoryWall\Upload\RecordCreator;
use App\Services\MemoryWallService;
use Throwable;

/**
 * Starts or resumes the control-plane state for one memory wall upload.
 *
 * The action coordinates the upload window, client metadata, idempotent session
 * lookup, and remote multipart session. Record creation, validation, and
 * cleanup stay behind their respective focused collaborators.
 */
final readonly class InitializeMemoryWallUpload
{
    /**
     * Create the upload initialization action.
     */
    public function __construct(
        private MemoryWallService $memoryWallService,
        private MetadataValidator $metadataValidator,
        private Authorizer $authorizer,
        private RecordCreator $recordCreator,
        private Cleanup $cleanup,
        private MemoryWallMultipartStorage $storage,
    ) {}

    /**
     * Initialize or resume an upload session for one browser-selected file.
     *
     * Repeating the same client upload ID is safe: completed and active
     * sessions are returned unchanged, while failed sessions are reset before
     * a new multipart session is created.
     */
    public function handle(Wedding $wedding, MemoryWallUploadInitializeData $data): MemoryWallUpload
    {
        $this->ensureMemoryWallIsOpen($wedding);
        $this->metadataValidator->validate($data->originalName, $data->size, $data->mimeType);

        $existingUpload = $this->existingUpload($wedding, $data->clientUploadId);

        if ($existingUpload !== null) {
            $this->authorizer->ensureTokenIsValid($existingUpload, $data->uploadToken);

            if ($existingUpload->status->isCompleted()) {
                $existingUpload->load('media');

                return $existingUpload;
            }

            if ($existingUpload->status->isUploading()) {
                $existingUpload->load('media');

                return $existingUpload;
            }

            $this->cleanup->resetFailedUpload($existingUpload);
            $upload = $existingUpload;
        } else {
            $upload = $this->recordCreator->create($wedding, $data);
        }

        // A database row without a matching remote session cannot be resumed.
        // Remove only the newly created state when object-storage initialization fails.
        try {
            $multipartUploadId = $this->storage->createMultipartUpload($upload->object_path, $data->mimeType);
        } catch (Throwable $exception) {
            if ($existingUpload === null) {
                $upload->delete();
            }

            throw $exception;
        }

        $upload->update([
            'multipart_upload_id' => $multipartUploadId,
            'status' => MemoryWallUploadStatus::Uploading,
            'error_message' => null,
        ]);

        $upload->refresh();
        $upload->load('media');

        return $upload;
    }

    /**
     * Retrieves an existing memory wall upload associated with the specified wedding
     * and client upload identifier.
     */
    protected function existingUpload(Wedding $wedding, string $clientUploadId): ?MemoryWallUpload
    {
        return $wedding->memoryWallUploads()
            ->where('client_upload_id', $clientUploadId)
            ->first();
    }

    /**
     * Prevent a new multipart session after the wedding's upload window closes.
     */
    private function ensureMemoryWallIsOpen(Wedding $wedding): void
    {
        abort_unless($this->memoryWallService->isFormOpen($wedding), 403);
    }
}
