<?php

declare(strict_types=1);

namespace App\Support\MediaLibrary;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

/**
 * Adds the owning model's snake-case name before Spatie's default media path.
 */
class ModelPathGenerator extends DefaultPathGenerator
{
    /**
     * Build the base path while preserving Spatie's default media key segment.
     */
    protected function getBasePath(Media $media): string
    {
        return "{$this->getModelPath($media)}/".parent::getBasePath($media);
    }

    /**
     * Resolve the folder belonging to the model owning the media item.
     */
    protected function getModelPath(Media $media): string
    {
        return Str::snake(class_basename($media->model_type));
    }
}
