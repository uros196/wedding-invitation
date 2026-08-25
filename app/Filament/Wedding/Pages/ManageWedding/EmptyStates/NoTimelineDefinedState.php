<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\EmptyStates;

use App\Filament\Wedding\Pages\ManageWedding\ManageWedding;
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
            ->heading(__('wedding.manage_wedding.timeline.not_defined'))
            ->icon(Heroicon::OutlinedClock)
            ->contained(false)
            ->visible(fn (Get $get) => empty($get('timelines') ?? []));

        if ($withAction) {
            $state->footer([
                Action::make('add_timeline')
                    ->label(__('Add Timeline'))
                    ->url(ManageWedding::getUrl(['tab' => 'schedule']).'#wedding-timeline')
                    ->button(),
            ]);
        }

        return $state;
    }
}
