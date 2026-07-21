<?php

namespace App\Filament\Resources\Groups\Schemas\Components\Form;

use App\Models\Group;
use App\Support\MetaFactory;
use Filament\Forms\Components\TextInput;

class MetaTitleInput
{
    /**
     * Generate meta title field.
     */
    public static function make(): TextInput
    {
        return TextInput::make('meta_title')
            ->label(__('Meta Title'))
            ->placeholder(fn (?Group $group) => resolve(MetaFactory::class)->forGroup($group)->title)
            ->maxLength(255);
    }
}
