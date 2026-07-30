<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

use Filament\Schemas\Components\Utilities\Get;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput;

class InvitationTitleInput
{
    /**
     * Generate invitation title input.
     */
    public static function make(): TextInput
    {
        return TextInput::make('invitation_title')
            ->label(__('Personalized Title'))
            ->requiredWith('invitation_message')
            ->columnSpanFull()
            ->live()
            ->showCharacterCounter(fn (Get $get) => filled($get('invitation_title')))
            ->maxLength(50);
    }
}
