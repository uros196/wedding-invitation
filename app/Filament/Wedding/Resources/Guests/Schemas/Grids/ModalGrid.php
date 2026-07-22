<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Guests\Schemas\Grids;

use App\Filament\Wedding\Resources\Guests\Schemas\Components\AgeSelect;
use App\Filament\Wedding\Resources\Guests\Schemas\Components\FirstNameInput;
use App\Filament\Wedding\Resources\Guests\Schemas\Components\GenderSelect;
use App\Filament\Wedding\Resources\Guests\Schemas\Components\LastNameInput;
use App\Filament\Wedding\Resources\Guests\Schemas\Components\NotesTextArea;
use App\Filament\Wedding\Resources\Guests\Schemas\Components\StatusSelect;
use Filament\Schemas\Components\Grid;

class ModalGrid
{
    /**
     * Get the modal grid schema.
     */
    public static function make(): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    FirstNameInput::make(),
                    LastNameInput::make(),
                ]),

            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    StatusSelect::make(),
                    AgeSelect::make(),
                    GenderSelect::make(),
                ]),

            NotesTextArea::make()
                ->rows(3),
        ];
    }
}
