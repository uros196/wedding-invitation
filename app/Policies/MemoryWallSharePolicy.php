<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MemoryWallShare;
use App\Models\User;

class MemoryWallSharePolicy
{
    /**
     * Determine whether the user can list links for their current wedding.
     */
    public function viewAny(User $user): bool
    {
        return $user->team?->wedding?->has_memory_wall === true;
    }

    /**
     * Determine whether the link belongs to the user's current wedding.
     */
    public function view(User $user, MemoryWallShare $memoryWallShare): bool
    {
        return $this->ownsShare($user, $memoryWallShare);
    }

    /**
     * Determine whether the user may create a link for their wedding.
     */
    public function create(User $user): bool
    {
        return $user->team?->wedding?->has_memory_wall === true;
    }

    /**
     * Determine whether the user may edit a link owned by their wedding.
     */
    public function update(User $user, MemoryWallShare $memoryWallShare): bool
    {
        return $this->ownsShare($user, $memoryWallShare);
    }

    /**
     * Determine whether the user may delete a link owned by their wedding.
     */
    public function delete(User $user, MemoryWallShare $memoryWallShare): bool
    {
        return $this->ownsShare($user, $memoryWallShare);
    }

    private function ownsShare(User $user, MemoryWallShare $memoryWallShare): bool
    {
        return $user->team?->wedding?->is($memoryWallShare->wedding) === true;
    }
}
