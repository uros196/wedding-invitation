<?php

declare(strict_types=1);

namespace App\Http\Resources\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'type' => $this->type,
            'extension' => $this->extension,
            'human_readable_size' => $this->human_readable_size,

            // Video uploads may not have a generated conversion; fall back to
            // the original URL so the gallery can still render them natively.
            'preview_url' => $this->getAvailableUrl(['preview']),
            'original_url' => $this->getUrl(),
            'size' => $this->size,
        ];
    }
}
