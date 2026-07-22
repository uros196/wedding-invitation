<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Guests\Schemas\Components;

use App\Filament\Wedding\Resources\Groups\Schemas\Components\Form\NameInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class GroupSelect
{
    /**
     * Generates a group select input.
     */
    public static function make(): Select
    {
        return Select::make('group_id')
            ->label(__('Group'))
            ->relationship(
                'group',
                'name',
                modifyQueryUsing: fn (Builder $query): Builder => $query->where(
                    'wedding_id',
                    auth()->user()?->team?->wedding?->id ?? -1,
                ),
            )
            ->required()
            ->exists('groups', 'id')
            ->searchable()
            ->preload()
            ->live()
            ->createOptionForm([
                NameInput::make(),
            ]);
    }
}
