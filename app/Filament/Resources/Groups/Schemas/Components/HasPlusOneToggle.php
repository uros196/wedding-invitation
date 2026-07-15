<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components;

use App\Models\Group;
use Filament\Forms\Components\Toggle;

class HasPlusOneToggle
{
    /**
     * Generate 'has_plus_one' toggle component.
     */
    public static function make(): Toggle
    {
        return Toggle::make('has_plus_one')
            ->label(__('messages.has_plus_one'))
            ->helperText(function (?Group $record): string {

                return ! $record?->hasOnlyOneGuest()
                    ? __('messages.has_plus_one_disabled_helper')
                    : __('messages.has_plus_one_description');
            })
            ->disabled(fn (?Group $record): bool => ! $record?->hasOnlyOneGuest());
    }
}
