<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas\Components\Form;

use Filament\Forms\Components\Textarea;

class InvitationMessageTextarea
{
    /**
     * Generate invitation message textarea component.
     */
    public static function make(): Textarea
    {
        return Textarea::make('invitation_message')
            ->label(__('messages.personalized_message'))
            ->placeholder(__('messages.personalized_message_placeholder'))
            ->rows(3)
            ->requiredWith('invitation_title')
            ->columnSpanFull();
    }
}
