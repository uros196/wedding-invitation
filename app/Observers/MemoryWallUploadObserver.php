<?php

declare(strict_types=1);

namespace App\Observers;

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
}
