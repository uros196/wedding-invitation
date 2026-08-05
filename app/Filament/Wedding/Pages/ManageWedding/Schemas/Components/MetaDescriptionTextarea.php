<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
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
            ->hintIcon(
                Heroicon::InformationCircle,
                __('wedding.manage_wedding.meta.description_help'),
            )
            ->live()
            ->maxLength(150)
            ->showCharacterCounter(fn (Get $get) => filled($get('meta_description')))
            ->showInsideControl()
            ->rows(3);
    }
}
