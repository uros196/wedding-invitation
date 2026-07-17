<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guests\Actions;

use App\Filament\Resources\Guests\Schemas\Grids\ModalGrid;
use App\Models\Guest;
use App\Services\GuestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

class AddCompanionAction
{
    /**
     * Generates the action to add a companion from the list
     * with possibility to create a new guest.
     */
    public static function make(): Action
    {
        $guestService = resolve(GuestService::class);

        return Action::make('addCompanion')
            ->label(__('Add companion'))
            ->modalHeading(__('Add companion'))
            ->modalDescription(__('Select an existing guest or create a new one.'))
            ->modalSubmitActionLabel(__('Add'))
            ->icon(Heroicon::Plus)
            ->schema([
                Select::make('guest_id')
                    ->label(__('Guest'))
                    ->placeholder(__('Search guests...'))
                    ->relationship('parent', 'full_name')
                    ->options(fn (?Guest $record) => $guestService->getCompanionOptions($record))
                    ->searchable()
                    ->live()
                    ->required()
                    ->exists('guests', 'id')
                    ->validationMessages([
                        'required' => __('You must select a guest or create a new one.'),
                    ])
                    ->createOptionForm(ModalGrid::make()),
            ])
            ->action(function (array $data, ?Guest $record, Action $action) use ($guestService) {
                if ($record) {
                    $guestService->assignCompanionToParent((int) $data['guest_id'], $record);

                    // Refresh list on the page
                    $record->refresh();
                }
            })
            ->visible(fn (?Guest $record) => $record?->hasCompanions());
    }
}
