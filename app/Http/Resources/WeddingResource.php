<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Wedding
 */
class WeddingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $isWeddingFinished = $this->wedding_date->isPast();

        return [
            'uuid' => $this->uuid,
            'bride_name' => $this->bride_name,
            'groom_name' => $this->groom_name,
            'hero_image' => $this->getHeroImageUrl('preview'),
            'wedding_day' => $this->wedding_date->dayName,
            'wedding_date' => $this->wedding_date->format(config('wedding.invitation.countdown.wedding_format')),
            'is_wedding_coming' => $this->wedding_date->isFuture(),
            'is_wedding_date' => $this->wedding_date->isToday(),
            'is_finished' => $isWeddingFinished,
            'rsvp_deadline' => $this->rsvp_deadline->format(config('wedding.invitation.countdown.rsvp_format')),
            'is_rsvp_open' => $this->is_rsvp_open,
            'countdown_due_datetime' => $this->countdown_due_datetime,
            'welcome_text' => $this->welcome_text,

            // Timelines
            'timelines_count' => $this->whenCounted('timelines'),
            'timelines' => WeddingTimelineResource::collection($this->whenLoaded('timelines')),

            // Memory Wall
            'has_memory_wall' => $this->has_memory_wall,
            'is_memory_wall_form_open' => $this->is_memory_wall_form_open,
            'is_memory_wall_finished' => $isWeddingFinished && !$this->is_memory_wall_form_open,
        ];
    }
}
