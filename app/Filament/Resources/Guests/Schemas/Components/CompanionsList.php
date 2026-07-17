<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guests\Schemas\Components;

use App\Filament\Resources\Guests\Actions\RemoveCompanionAction;
use App\Models\Guest;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\UnorderedList;
use Filament\Support\Icons\Heroicon;

class CompanionsList
{
    /**
     * Generates a list of companions with actions.
     * This component is visible when the guest record exists and has companions.
     */
    public static function make(): UnorderedList
    {
        return UnorderedList::make(function (?Guest $record) {
            if (! $record) {
                return [];
            }

            return $record->companions()->get()->map(function (Guest $companion) {
                return Flex::make([
                    Text::make($companion->full_name)
                        ->icon(Heroicon::User)
                        ->grow(),

                    Text::make($companion->age?->getLabel())
                        ->badge()
                        ->color('gray')
                        ->visible(fn () => $companion->age !== null),

                    RemoveCompanionAction::make("removeCompanion_{$companion->id}")
                        ->record($companion)
                        ->link(),
                ])
                    ->key("companion_{$companion->id}")
                    ->columnSpanFull();
            })->toArray();
        })
            ->columns(1)
            ->visible(fn (?Guest $record) => $record?->hasCompanions());
    }
}
