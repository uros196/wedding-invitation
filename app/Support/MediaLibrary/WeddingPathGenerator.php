<?php

declare(strict_types=1);

namespace App\Support\MediaLibrary;

use App\Models\Wedding;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Adds the wedding UUID between the model folder and Spatie's default path.
 */
class WeddingPathGenerator extends ModelPathGenerator
{
    /**
     * Resolve the wedding-specific folder for the media item.
     */
    protected function getModelPath(Media $media): string
    {
        $wedding = Wedding::withoutPublish()->findOrFail($media->model_id);

        return parent::getModelPath($media)."/{$wedding->uuid}";
    }
}
