<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\DateTimePicker;

class RSVPDeadlinePicker
{
    /**
     * Generate the date time picker for the RSVP deadline.
     */
    public static function make(): DateTimePicker
    {
        return DateTimePicker::make('rsvp_deadline')
            ->label(__('RSVP Deadline'))
            ->required();
    }
}
