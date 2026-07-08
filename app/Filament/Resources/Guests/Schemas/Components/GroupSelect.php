<?php

namespace App\Filament\Resources\Guests\Schemas\Components;

use App\Filament\Resources\Groups\Schemas\Components\DescriptionTextarea;
use App\Filament\Resources\Groups\Schemas\Components\NameInput;
use Filament\Forms\Components\Select;

class GroupSelect
{
    /**
     * Generates a group select input.
     */
    public static function make(): Select
    {
        return Select::make('group_id')
            ->label('Grupa')
            ->relationship('group', 'name')
            ->required()
            ->exists('groups', 'id')
            ->searchable()
            ->preload()
            ->live()
            ->createOptionForm([
                NameInput::make(),
                DescriptionTextarea::make(),
            ]);
    }
}
