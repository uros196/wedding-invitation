<?php

namespace App\Filament\Resources\Guests\Schemas\Components;

use App\Services\GuestService;
use Filament\Forms\Components\Repeater;

class CompanionsRepeater
{
    /**
     * Generates a companions' repeater.
     */
    public static function make(): Repeater
    {
        return Repeater::make('companions')
            ->relationship('companions')
            ->schema([
                FirstNameInput::make(),
                LastNameInput::make(),
                StatusSelect::make()
                    ->label('Status'),
                AgeSelect::make(),
                GenderSelect::make(),
            ])
            ->columns(1)
            ->itemLabel(fn (array $state): ?string => app(GuestService::class)->getCompanionItemLabel($state))
            ->defaultItems(0)
            ->addActionLabel('Novi kompanjon');
    }
}
