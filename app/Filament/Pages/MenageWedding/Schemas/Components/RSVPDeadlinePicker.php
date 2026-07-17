<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Carbon;

class RSVPDeadlinePicker
{
    /**
     * Generate the date time picker for the RSVP deadline.
     */
    public static function make(): DateTimePicker
    {
        return DateTimePicker::make('rsvp_deadline')
            ->label(__('RSVP Deadline'))
            ->maxDate(fn (Get $get) => filled($get('wedding_date'))
                ? Carbon::parse($get('wedding_date'))->endOfDay()
                : null)
            ->required();
    }
}
