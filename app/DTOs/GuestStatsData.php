<?php

namespace App\DTOs;

use App\Enums\GuestStatus;
use App\Models\Guest;

final readonly class GuestStatsData
{
    public function __construct(
        public int $confirmedGuestsCount,
        public int $declinedGuestsCount,
        public int $pendingGuestsCount,
    ) {}

    /**
     * Make data object using default queries/counts.
     */
    public static function make(): self
    {
        return new self(
            confirmedGuestsCount: Guest::whereStatus(GuestStatus::Confirmed)->count(),
            declinedGuestsCount: Guest::whereStatus(GuestStatus::Declined)->count(),
            pendingGuestsCount: Guest::whereStatus(GuestStatus::Pending)->count(),
        );
    }
}
