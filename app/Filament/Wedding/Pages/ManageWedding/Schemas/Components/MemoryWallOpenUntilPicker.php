<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class MemoryWallOpenUntilPicker
{
    /**
     * Generate the date time picker for the memory wall open until date.
     */
    public static function make(): DateTimePicker
    {
        return DateTimePicker::make('memory_wall_open_until')
            ->label(__('Memory Wall Open Until'))
            ->placeholder(__('wedding.manage_wedding.memory_wall.open_until_placeholder'))
            ->hintIcon(
                Heroicon::InformationCircle,
                __('wedding.manage_wedding.memory_wall.open_until_help'),
            )
            ->disabled(fn (Get $get): bool => ! $get('has_memory_wall'))
            ->minDate(fn (Get $get) => filled($get('wedding_date'))
                ? Carbon::parse($get('wedding_date'))->endOfDay()
                : null
            )
            ->maxDate(fn (Get $get) => filled($get('wedding_date'))
                ? static::getFormOpenForMax($get('wedding_date'))
                : null
            );
    }

    /**
     * Calculate the maximum date for the memory wall form to remain open based on the wedding date.
     */
    protected static function getFormOpenForMax(string $weddingDate): Carbon
    {
        return Carbon::parse($weddingDate)
            ->addDays(config('wedding.invitation.memory_wall.form_open_for_max'))
            ->endOfDay();
    }
}
