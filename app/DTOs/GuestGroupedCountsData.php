<?php

namespace App\DTOs;

use App\Models\Guest;
use App\Services\GuestService;

final readonly class GuestGroupedCountsData
{
    public function __construct(
        public array $guestsByAge,
        public array $guestsByGender,
        public int $totalGuestsCount,
    ) {}

    /**
     * Creates and returns a new instance of the class.
     */
    public static function make(): self
    {
        $guestService = resolve(GuestService::class);

        return new self(
            guestsByAge: $guestService->getCountsByAge(),
            guestsByGender: $guestService->getCountsByGender(),
            totalGuestsCount: Guest::count(),
        );
    }
}
