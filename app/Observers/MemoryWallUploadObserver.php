<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\MemoryWallUpload;

class MemoryWallUploadObserver
{
    /**
     * Remove the media record when an upload session is deleted from Filament
     * or canceled before completion.
     */
    public function deleting(MemoryWallUpload $model): void
    {
        $model->media?->delete();
    }
}
