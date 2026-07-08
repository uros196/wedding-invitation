<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Database\Eloquent\Builder;

class GroupService
{
    /**
     * Check if the record exists (for field visibility).
     */
    public function isRecordExists(?Group $record): bool
    {
        return $record !== null;
    }

    /**
     * Get the most viewed groups.
     */
    public function getMostViewedGroups(int $limit = 5): Builder
    {
        return Group::query()->orderByDesc('views_count')->limit($limit);
    }
}
