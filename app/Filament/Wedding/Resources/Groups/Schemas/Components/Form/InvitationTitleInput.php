<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

use Filament\Forms\Components\TextInput;

class InvitationTitleInput
{
    /**
     * Generate invitation title input.
     */
    public static function make(): TextInput
    {
        return TextInput::make('invitation_title')
            ->label(__('messages.personalized_title'))
            ->requiredWith('invitation_message')
            ->columnSpanFull();
    }
}
