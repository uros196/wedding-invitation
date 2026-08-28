<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

/**
 * Builds the setting that enables or disables the public memory wall.
 */
class MemoryWallToggle
{
    /**
     * Generate the toggle for the memory wall.
     */
    public static function make(): Toggle
    {
        return Toggle::make('has_memory_wall')
            ->label(__('Enable Memory Wall'))
            ->hintIcon(
                Heroicon::InformationCircle,
                __('wedding.manage_wedding.memory_wall.enable_help'),
            )
            ->live()
            ->default(true);
    }
}
