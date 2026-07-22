<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Group
 */
class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'invitation_title' => $this->invitation_title,
            'invitation_message' => $this->invitation_message,
            'has_plus_one' => $this->has_plus_one,
            'guests_count' => $this->guests_count,
            'has_single_guest' => $this->guests_count === 1,
            'guests' => GuestResource::collection($this->whenLoaded('guests')),
        ];
    }
}
