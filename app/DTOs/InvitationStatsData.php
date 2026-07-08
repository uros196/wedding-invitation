<?php

namespace App\DTOs;

use App\Models\Group;
use App\Models\Wedding;

final readonly class InvitationStatsData
{
    public function __construct(
        public int $sentInvitationsCount,
        public int $totalViews,
        public ?Wedding $wedding = null
    ) {}

    /**
     * Make data object using default queries/counts.
     */
    public static function make(): self
    {
        return new self(
            sentInvitationsCount: Group::whereIsSent(true)->count(),
            totalViews: (int) Group::sum('views_count'),
            wedding: Wedding::first(),
        );
    }
}
