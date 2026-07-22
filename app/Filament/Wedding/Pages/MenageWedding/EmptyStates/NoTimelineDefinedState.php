<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\MenageWedding\EmptyStates;

use App\Filament\Wedding\Pages\MenageWedding\ManageWedding;
use Filament\Actions\Action;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class NoTimelineDefinedState
{
    /**
     * Make an empty state for the timeline section.
     */
    public static function make(bool $withAction = true): EmptyState
    {
        $state = EmptyState::make('no_timeline')
            ->heading(__('Timeline is not defined yet.'))
            ->icon(Heroicon::OutlinedClock)
            ->contained(false)
            ->visible(fn (Get $get) => empty($get('timelines') ?? []));

        if ($withAction) {
            $state->footer([
                Action::make('add_timeline')
                    ->label(__('Add Timeline'))
                    ->url(ManageWedding::getUrl())
                    ->button(),
            ]);
        }

        return $state;
    }
}
