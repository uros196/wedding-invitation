<?php

namespace App\Policies;

use App\Models\User;

class MessagePolicy
{
    /**
     * Determine if the user has access based on their published wedding status.
     */
    public function access(User $user): bool
    {
        return $user->hasPublishedWedding();
    }
}
