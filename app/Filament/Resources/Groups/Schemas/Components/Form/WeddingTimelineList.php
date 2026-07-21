<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Form;

use App\Models\Group;
use App\Models\WeddingTimeline;
use App\Services\GroupService;
use App\Services\WeddingTimelineService;
use Filament\Forms\Components\CheckboxList;

class WeddingTimelineList
{
    /**
     * Generate wedding timeline checkbox list.
     */
    public static function make(): CheckboxList
    {
        $service = resolve(WeddingTimelineService::class);
        $groupService = resolve(GroupService::class);

        return CheckboxList::make('visible_timeline_items')
            ->label(__('Timeline Items'))
            ->options(fn () => $service->getList())
            ->afterStateHydrated(function ($component, ?Group $record) use ($groupService) {
                $component->state(
                    $groupService->getAvailableTimeline($record)->pluck('id')->toArray()
                );
            })
            ->saveRelationshipsUsing(fn (?Group $record, $state) => $groupService->syncTimeline($record, $state))
            ->visible(fn () => WeddingTimeline::exists())
            ->columns(2)
            ->gridDirection('vertical');
    }
}
