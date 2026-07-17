<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\WeddingTimeline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WeddingTimeline
 */
class WeddingTimelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'address' => $this->address,
            'time' => $this->time,
            'map_url' => $this->map_url,
            'icon' => $this->icon,
        ];
    }
}
