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
        return $user->hasWedding();
    }
}
