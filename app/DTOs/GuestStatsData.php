<?php

namespace App\DTOs;

use App\Enums\GuestStatus;
use App\Models\Guest;
use Illuminate\Support\Collection;

final readonly class GuestStatsData
{
    public function __construct(
        public int $confirmedGuestsCount,
        public int $declinedGuestsCount,
        public int $pendingGuestsCount,
    ) {}

    /**
     * Make data object using default queries/counts.
     *
     * @param Collection<Guest> $guests
     */
    public static function make(Collection $guests): self
    {
        return new self(
            confirmedGuestsCount: $guests->firstWhere('status', GuestStatus::Confirmed)?->aggregate ?? 0,
            declinedGuestsCount: $guests->firstWhere('status', GuestStatus::Declined)?->aggregate ?? 0,
            pendingGuestsCount: $guests->firstWhere('status', GuestStatus::Pending)?->aggregate ?? 0,
        );
    }
}
