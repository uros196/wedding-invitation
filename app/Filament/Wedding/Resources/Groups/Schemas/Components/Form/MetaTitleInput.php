<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

use App\Models\Group;
use App\Support\MetaFactory;
use Filament\Schemas\Components\Utilities\Get;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput;

class MetaTitleInput
{
    /**
     * Generate meta title field.
     */
    public static function make(): TextInput
    {
        return TextInput::make('meta_title')
            ->label(__('Meta Title'))
            ->live()
            ->placeholder(fn (?Group $group) => resolve(MetaFactory::class)->forGroup($group)->title)
            ->showCharacterCounter(fn (Get $get) => filled($get('meta_title')))
            ->maxLength(60);
    }
}
