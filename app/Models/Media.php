<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasReady;
use App\Enums\MediaStatus;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Application media model with a default visibility state for direct uploads.
 *
 * @property MediaStatus $status
 */
class Media extends BaseMedia
{
    use HasReady;

    /**
     * Keep the casts provided by Spatie and add the application lifecycle state.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
        'status' => MediaStatus::class,
    ];
}
