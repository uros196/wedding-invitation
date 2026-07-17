<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\Textarea;

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
            ->rows(3);
    }
}
