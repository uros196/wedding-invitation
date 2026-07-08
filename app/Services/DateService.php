<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use DateInterval;
use Illuminate\Support\Carbon;

class DateService
{
    /**
     * Calculates the difference between the current date and the countdown date.
     */
    public function diff(Carbon|CarbonImmutable $date): DateInterval
    {
        return now()->diff($date);
    }

    /**
     * Format time difference.
     */
    public function formatDiff(DateInterval $diff): string
    {
        if ($diff->days > 0) {
            return $diff->days.' days';
        }

        if ($diff->h > 0) {
            return $diff->h.' hours';
        }

        return $diff->i.' minutes';
    }
}
