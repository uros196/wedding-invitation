<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WeddingTimeline;
use Illuminate\Support\Collection;

class WeddingTimelineService
{
    /**
     * Get the formatted list of wedding timelines.
     */
    public function getList(): Collection
    {
        return WeddingTimeline::visible()
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->id => $item->list_name]);
    }

    /**
     * Retrieve the list of available wedding timeline IDs.
     */
    public function getAvailableList(bool $onlyIds = true): array|Collection
    {
        $timeline = WeddingTimeline::visible()->get();

        return $onlyIds ? $timeline->pluck('id')->toArray() : $timeline;
    }
}
