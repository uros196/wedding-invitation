<?php

declare(strict_types=1);

namespace App\Support\MediaLibrary;

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
        return parent::getModelPath($media)."/{$media->model->uuid}";
    }
}
