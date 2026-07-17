<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\DatePicker;

class WeddingDatePicker
{
    /**
     * Generate the date picker for the wedding date.
     */
    public static function make(): DatePicker
    {
        return DatePicker::make('wedding_date')
            ->label(__('Wedding Date and Time'))
            ->required();
    }
}
