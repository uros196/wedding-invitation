<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput;

class MetaTitleInput
{
    /**
     * Generate meta title input.
     */
    public static function make(): TextInput
    {
        return TextInput::make('meta_title')
            ->label(__('Meta Title'))
            ->placeholder(__(config('wedding.meta.title')))
            ->hintIcon(
                Heroicon::InformationCircle,
                __('wedding.manage_wedding.meta.title_help'),
            )
            ->live()
            ->showCharacterCounter(fn (Get $get) => filled($get('meta_title')))
            ->maxLength(60);
    }
}
