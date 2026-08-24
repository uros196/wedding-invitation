<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class GroupPolicy
{
    /**
     * Determine if the user can create a group.
     */
    public function create(User $user): bool
    {
        return $user->hasPublishedWedding();
    }

    /**
     * Determine if the user has access based on their published wedding status.
     */
    public function access(User $user): bool
    {
        return $user->hasPublishedWedding();
    }
}
