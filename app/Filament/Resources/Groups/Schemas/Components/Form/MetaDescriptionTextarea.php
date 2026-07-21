<?php

namespace App\Filament\Resources\Groups\Schemas\Components\Form;

use App\Models\Group;
use App\Support\MetaFactory;
use Filament\Forms\Components\Textarea;

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
            ->rows(3);
    }
}
