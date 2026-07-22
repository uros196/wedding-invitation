<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

use App\Models\Group;
use App\Services\GroupService;
use App\Services\WeddingService;
use Filament\Forms\Components\CheckboxList;

class WeddingTimelineList
{
    /**
     * Generate a wedding timeline checkbox list.
     */
    public static function make(): CheckboxList
    {
        $groupService = resolve(GroupService::class);

        return CheckboxList::make('visible_timeline_items')
            ->label(__('Timeline Items'))
            ->options(fn (?Group $record) => self::timelineList($record))
            ->afterStateHydrated(function ($component, ?Group $record) {
                $component->state(self::availableTimeline($record));
            })
            ->saveRelationshipsUsing(fn (?Group $record, $state) => $groupService->syncTimeline($record, $state))
            ->visible(fn (?Group $record) => $record?->hasWedding())
            ->columns(2)
            ->gridDirection('vertical');
    }

    /**
     * Get the timeline list attached to the wedding.
     */
    protected static function timelineList(?Group $group): array
    {
        $weddingService = resolve(WeddingService::class);

        return $weddingService->timelineList($group?->wedding)
            ->pluck('list_name', 'id')
            ->toArray();
    }

    /**
     * Retrieve the available timeline for the specified group.
     */
    protected static function availableTimeline(?Group $group): array
    {
        $groupService = resolve(GroupService::class);

        return $groupService->getAvailableTimeline($group)
            ->pluck('id')
            ->toArray();
    }
}
