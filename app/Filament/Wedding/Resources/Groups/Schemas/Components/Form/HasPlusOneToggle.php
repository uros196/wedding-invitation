<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

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
            ->label(__('wedding.groups.plus_one.label'))
            ->helperText(function (?Group $record): string {

                return ! $record?->hasOnlyOneGuest()
                    ? __('wedding.groups.plus_one.disabled_helper')
                    : __('wedding.groups.plus_one.description');
            })
            ->disabled(fn (?Group $record): bool => ! $record?->hasOnlyOneGuest());
    }
}
