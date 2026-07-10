<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guests\Schemas;

use App\Filament\Resources\Guests\Actions\AddCompanionAction;
use App\Filament\Resources\Guests\EmptyStates\NoCompanionAddedState;
use App\Filament\Resources\Guests\EmptyStates\NoGuestCreatedState;
use App\Filament\Resources\Guests\Schemas\Components\AgeSelect;
use App\Filament\Resources\Guests\Schemas\Components\CompanionsList;
use App\Filament\Resources\Guests\Schemas\Components\FirstNameInput;
use App\Filament\Resources\Guests\Schemas\Components\GenderSelect;
use App\Filament\Resources\Guests\Schemas\Components\GroupSelect;
use App\Filament\Resources\Guests\Schemas\Components\LastNameInput;
use App\Filament\Resources\Guests\Schemas\Components\NotesTextArea;
use App\Filament\Resources\Guests\Schemas\Components\StatusSelect;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Form schema configuration for the Guest resource.
 */
class GuestForm
{
    /**
     * Configure the form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.guest_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FirstNameInput::make(),
                                LastNameInput::make(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                GroupSelect::make(),
                                StatusSelect::make(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                AgeSelect::make(),
                                GenderSelect::make(),
                            ]),

                        NotesTextArea::make(),
                    ])
                    ->columnSpan(1),

                Section::make(__('messages.companions'))
                    ->key('companions_section')
                    ->description(__('messages.companions_description'))
                    ->headerActions([
                        AddCompanionAction::make(),
                    ])
                    ->schema([
                        NoGuestCreatedState::make(),
                        NoCompanionAddedState::make(),
                        CompanionsList::make(),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
