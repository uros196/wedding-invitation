<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use Filament\Schemas\Components\Utilities\Get;
use Schmeits\FilamentCharacterCounter\Forms\Components\Textarea;

class MetaDescriptionTextarea
{
    /**
     * Generate meta description textarea.
     */
    public static function make(): Textarea
    {
        return Textarea::make('meta_description')
            ->label(__('Meta Description'))
            ->placeholder(__(config('wedding.meta.description')))
            ->live()
            ->maxLength(150)
            ->showCharacterCounter(fn (Get $get) => filled($get('meta_description')))
            ->showInsideControl()
            ->rows(3);
    }
}
