<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendMemoryWallUploadDigestJob;
use App\Models\Media;
use App\Models\MemoryWallUpload;

class MemoryWallUploadObserver
{
    /**
     * Remove the media record when an upload session is deleted from Filament
     * or canceled before completion.
     */
    public function deleting(MemoryWallUpload $model): void
    {
        if ($model->media_id === null) {
            return;
        }

        Media::withoutReady()->find($model->media_id)?->delete();
    }

    /**
     * Start the trailing-edge quiet period after an upload becomes visible.
     */
    public function updated(MemoryWallUpload $model): void
    {
        if (! $model->wasChanged('status') || ! $model->status->isCompleted()) {
            return;
        }

        SendMemoryWallUploadDigestJob::dispatch((int) $model->wedding_id)
            ->delay(now()->addMinutes((int) config('memory-wall.digest_delay_minutes', 10)))
            ->afterCommit();
    }
}
