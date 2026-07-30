<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Actions;

use App\Models\Group;
use Filament\Actions\Action;

class ToggleInvitationSentAction
{
    /**
     * Create the toggle invitation sent action.
     */
    public static function make(): Action
    {
        return Action::make('toggleInvitationSent')
            ->label(fn (Group $record): string => $record->is_sent
                ? __('Invitation Sent')
                : __('Invitation Not Sent'))
            ->color(fn (Group $record): string => $record->is_sent ? 'success' : 'gray')
            ->action(function (Group $record): void {
                $record->update([
                    'is_sent' => ! $record->is_sent,
                ]);
            });
    }
}
