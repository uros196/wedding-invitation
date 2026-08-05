<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

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
            ->placeholder(__('wedding.manage_wedding.invitation_text.welcome_text_placeholder'))
            ->extraInputAttributes(['style' => 'min-height: 300px;'])
            ->required();
    }
}
