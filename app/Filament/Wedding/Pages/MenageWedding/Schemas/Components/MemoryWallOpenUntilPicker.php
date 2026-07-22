<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;

class MemoryWallOpenUntilPicker
{
    /**
     * Generate the date time picker for the memory wall open until date.
     */
    public static function make(): DateTimePicker
    {
        return DateTimePicker::make('memory_wall_open_until')
            ->label(__('Memory Wall Open Until'))
            ->disabled(fn (Get $get): bool => ! $get('has_memory_wall'));
    }
}
