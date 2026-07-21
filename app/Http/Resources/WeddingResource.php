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
        return [
            'bride_name' => $this->bride_name,
            'groom_name' => $this->groom_name,
            'hero_image' => $this->getHeroImageUrl(),
            'wedding_day' => $this->wedding_date->dayName,
            'wedding_date' => $this->wedding_date->format(config('wedding.invitation.countdown.wedding_format')),
            'rsvp_deadline' => $this->rsvp_deadline->format(config('wedding.invitation.countdown.rsvp_format')),
            'is_rsvp_open' => $this->is_rsvp_open,
            'countdown_due_datetime' => $this->countdown_due_datetime,
            'welcome_text' => $this->welcome_text,
            'timelines_count' => $this->timelines_count,
            'timelines' => WeddingTimelineResource::collection($this->timelines),
        ];
    }
}
