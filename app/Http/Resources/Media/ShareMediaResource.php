<?php

declare(strict_types=1);

namespace App\Http\Resources\Media;

use App\Contracts\MemoryWallMediaUrl;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Exposes only short-lived URLs for media authorized through a share link.
 *
 * @mixin Media
 */
class ShareMediaResource extends JsonResource
{
    public function __construct(
        mixed $resource,
        private readonly bool $allowDownloads = false,
        private readonly ?MemoryWallMediaUrl $mediaUrl = null,
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform one authorized share media record.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $expiresAt = now()->addMinutes((int) config('memory-wall.download_url_minutes', 60));

        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'type' => $this->type,
            'extension' => $this->extension,
            'human_readable_size' => $this->human_readable_size,
            'preview_url' => $this->getAvailableTemporaryUrl(['preview'], $expiresAt),
            'original_url' => $this->getTemporaryUrl($expiresAt),
            'size' => $this->size,
        ];

        if ($this->allowDownloads) {
            $data['download_url'] = ($this->mediaUrl ?? resolve(MemoryWallMediaUrl::class))
                ->make($this->resource, $this->file_name);
        }

        return $data;
    }
}
