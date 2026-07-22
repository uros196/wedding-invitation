<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class WeddingPolicy
{
    /**
     * Determine whether the user can enable the memory wall.
     */
    public function enableMemoryWall(User $user): bool
    {
        return $user->team->has_memory_wall;
    }
}
