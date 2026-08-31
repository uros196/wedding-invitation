<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MemoryWallUploadStatus;
use App\Events\MemoryWallUploadProcessed;
use App\Http\Resources\Media\MediaResource;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\CompleteMemoryWallUpload;
use App\Services\MemoryWall\Upload\Cleanup;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Completes and publishes a Memory Wall upload outside the HTTP request.
 */
final class CompleteMemoryWallUploadJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Maximum number of attempts for transient storage failures.
     */
    public int $tries = 3;

    /**
     * Maximum runtime needed for large object-storage operations.
     */
    public int $timeout = 900;

    /**
     * Duration for which another completion job for this upload is locked.
     */
    public int $uniqueFor = 3600;

    /**
     * Create a completion job for an authorized upload session.
     */
    public function __construct(
        public Wedding $wedding,
        public MemoryWallUpload $upload,
    ) {}

    /**
     * Complete the upload only once while allowing retries after a worker error.
     */
    public function handle(CompleteMemoryWallUpload $completeUpload, Cleanup $cleanup): void
    {
        $upload = $this->upload->fresh();

        if ($upload === null || $upload->status->isCompleted()) {
            return;
        }

        if ($upload->status->isFailed()) {
            $this->broadcastFailure($upload);

            return;
        }

        if ($upload->status->isUploading()) {
            MemoryWallUpload::query()
                ->whereKey($upload->getKey())
                ->where('status', MemoryWallUploadStatus::Uploading->value)
                ->update(['status' => MemoryWallUploadStatus::Processing]);

            $upload->refresh();
        }

        if (! $upload->status->isProcessing()) {
            return;
        }

        $wedding = $this->wedding->fresh();

        if ($wedding === null) {
            return;
        }

        try {
            $media = $completeUpload->finalize($wedding, $upload);
        } catch (ValidationException) {
            $upload->refresh();

            if (! $upload->status->isFailed()) {
                $cleanup->markAsFailed(
                    $upload,
                    __('wedding.memory_wall.validation.processing_failed'),
                );
            }

            $this->broadcastFailure($upload->fresh() ?? $upload);

            return;
        }

        $this->broadcastCompletion($upload, $media);
    }

    /**
     * Retry transient object-storage failures with increasing delays.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Keep the control-plane record visible after all retries are exhausted.
     */
    public function failed(?Throwable $exception): void
    {
        $upload = $this->upload->fresh();

        if ($upload === null || $upload->status->isCompleted()) {
            return;
        }

        Log::error('Memory Wall upload completion failed.', [
            'upload_id' => $upload->getKey(),
            'upload_uuid' => $upload->uuid,
            'wedding_id' => $upload->wedding_id,
            'exception' => $exception?->getMessage(),
        ]);

        if (! $upload->status->isFailed()) {
            app(Cleanup::class)->markAsFailed(
                $upload,
                __('wedding.memory_wall.validation.processing_failed'),
            );
        }

        $this->broadcastFailure($upload->fresh() ?? $upload);
    }

    /**
     * Use one unique queue lock per upload session.
     */
    public function uniqueId(): string
    {
        return $this->upload->uuid;
    }

    /**
     * Notify the browser after the Media Library record becomes visible.
     */
    private function broadcastCompletion(MemoryWallUpload $upload, Media $media): void
    {
        MemoryWallUploadProcessed::dispatch(
            $upload->uuid,
            MemoryWallUploadStatus::Completed,
            MediaResource::make($media)->resolve(request()),
        );
    }

    /**
     * Notify the browser after the upload reaches a terminal failure state.
     */
    private function broadcastFailure(MemoryWallUpload $upload): void
    {
        MemoryWallUploadProcessed::dispatch(
            $upload->uuid,
            MemoryWallUploadStatus::Failed,
            error: $upload->error_message ?? __('wedding.memory_wall.validation.processing_failed'),
        );
    }
}
