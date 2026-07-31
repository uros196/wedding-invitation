<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

use Filament\Schemas\Components\Utilities\Get;
use Schmeits\FilamentCharacterCounter\Forms\Components\Textarea;

class InvitationMessageTextarea
{
    /**
     * Generate invitation message textarea component.
     */
    public static function make(): Textarea
    {
        return Textarea::make('invitation_message')
            ->label(__('Personalized Message'))
            ->placeholder(__('wedding.groups.invitation.personalized_message_placeholder'))
            ->rows(3)
            ->requiredWith('invitation_title')
            ->live()
            ->maxLength(500)
            ->showCharacterCounter(fn (Get $get) => filled($get('invitation_message')))
            ->showInsideControl()
            ->columnSpanFull();
    }
}
