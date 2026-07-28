<?php

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

use App\Models\Group;
use App\Support\MetaFactory;
use Filament\Schemas\Components\Utilities\Get;
use Schmeits\FilamentCharacterCounter\Forms\Components\Textarea;

class MetaDescriptionTextarea
{
    /**
     * Generate a meta description textarea component.
     */
    public static function make(): Textarea
    {
        return Textarea::make('meta_description')
            ->label(__('Meta Description'))
            ->placeholder(fn (?Group $group) => resolve(MetaFactory::class)->forGroup($group)->description)
            ->live()
            ->maxLength(150)
            ->showCharacterCounter(fn (Get $get) => filled($get('meta_description')))
            ->showInsideControl()
            ->rows(3);
    }
}
