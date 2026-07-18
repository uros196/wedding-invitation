<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\WeddingTimeline;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
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
            'time' => $this->time?->format('H:i'),
            'map_url' => $this->map_url,
            'icon' => $this->frontendIconName(),
        ];
    }

    /**
     * Resolve the icon name expected by the frontend icon registry.
     *
     * Lucide icons are stored as kebab-case values (e.g. "alarm-clock") but the
     * `lucide-react` icon map on the frontend is keyed by PascalCase names
     * (e.g. "AlarmClock"), which matches the enum case name. Custom icons that
     * are not part of the Lucide set (e.g. "rings") are returned unchanged.
     */
    protected function frontendIconName(): ?string
    {
        if (blank($this->icon)) {
            return null;
        }

        return LucideIcon::tryFrom($this->icon)?->name ?? $this->icon;
    }
}
