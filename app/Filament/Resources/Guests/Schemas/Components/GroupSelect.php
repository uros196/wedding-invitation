<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guests\Schemas\Components;

use App\Filament\Resources\Groups\Schemas\Components\Form\NameInput;
use Filament\Forms\Components\Select;

class GroupSelect
{
    /**
     * Generates a group select input.
     */
    public static function make(): Select
    {
        return Select::make('group_id')
            ->label(__('Group'))
            ->relationship('group', 'name')
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
