<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MemoryWallUpload;
use App\Models\User;
use App\Services\MemoryWallService;

class MemoryWallUploadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MemoryWallUpload $memoryWallUpload): bool
    {
        return $this->canAccess($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MemoryWallUpload $memoryWallUpload): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MemoryWallUpload $memoryWallUpload): bool
    {
        return $this->canAccess($user);
    }

    /**
     * Determine whether the user has access based on their wedding and memory wall enabled status.
     */
    protected function canAccess(User $user): bool
    {
        $wedding = $user->team?->wedding;

        return $wedding !== null
            && resolve(MemoryWallService::class)->isEnabled($wedding);
    }
}
