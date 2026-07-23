<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use Filament\Forms\Components\Toggle;

class MemoryWallToggle
{
    /**
     * Generate the toggle for the memory wall.
     */
    public static function make(): Toggle
    {
        return Toggle::make('has_memory_wall')
            ->label(__('Enable Memory Wall'))
            ->live()
            ->default(true);
    }
}
