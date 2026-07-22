<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\RichEditor;

class WelcomeTextRichEditor
{
    /**
     * Generate the rich editor for the welcome text.
     */
    public static function make(): RichEditor
    {
        return RichEditor::make('welcome_text')
            ->label(__('Main Text'))
            ->extraInputAttributes(['style' => 'min-height: 300px;'])
            ->required();
    }
}
