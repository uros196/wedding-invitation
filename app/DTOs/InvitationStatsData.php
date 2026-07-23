<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Wedding;

final readonly class InvitationStatsData
{
    public function __construct(
        public int $sentInvitationsCount,
        public int $totalViews,
        public ?Wedding $wedding = null
    ) {}

    /**
     * Make an InvitationStatsData instance based on a given Wedding.
     */
    public static function make(?Wedding $wedding = null): self
    {
        return new self(
            sentInvitationsCount: (int) ($wedding?->sent_invitations_count ?? 0),
            totalViews: (int) ($wedding?->groups_sum_views_count ?? 0),
            wedding: $wedding,
        );
    }
}
