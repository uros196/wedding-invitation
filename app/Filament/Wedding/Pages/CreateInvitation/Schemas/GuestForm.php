<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\CreateInvitation\Schemas;

use App\Filament\Wedding\Resources\Guests\Schemas\Components\AgeSelect;
use App\Filament\Wedding\Resources\Guests\Schemas\Components\FirstNameInput;
use App\Filament\Wedding\Resources\Guests\Schemas\Components\LastNameInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Form schema for adding guests during invitation creation.
 */
class GuestForm
{
    /**
     * Configure the minimal guest form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Guest Information'))
                ->description(__('wedding.groups.quick_create.guest.form_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            FirstNameInput::make(),
                            LastNameInput::make(),
                        ]),

                    AgeSelect::make(),
                ]),
        ]);
    }
}
