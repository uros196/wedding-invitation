<?php

namespace App\Policies;

use App\Models\User;

class GuestPolicy
{
    /**
     * Determine if the user can create a group.
     */
    public function create(User $user): bool
    {
        return $user->hasWedding();
    }
}
