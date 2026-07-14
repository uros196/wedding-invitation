<?php

namespace App\Filament\Resources\Groups\Schemas\Components;

use App\Models\Group;
use App\Services\GroupService;
use Filament\Forms\Components\TextInput;

class UuidInput
{
    /**
     * Generate a UUID input field component.
     */
    public static function make(): TextInput
    {
        return TextInput::make('uuid')
            ->label(__('UUID (Invitation Link)'))
            ->disabled()
            ->dehydrated(false)
            ->visible(fn (?Group $record): bool => app(GroupService::class)->isRecordExists($record))
            ->columnSpan(1);
    }
}
