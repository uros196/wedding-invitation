<?php

namespace App\Observers;

use App\Models\Group;
use Illuminate\Support\Str;

class GroupObserver
{
    /**
     * Handle the Group "creating" event.
     */
    public function creating(Group $group): void
    {
        if (empty($group->uuid)) {
            $group->uuid = (string) Str::uuid();
        }
    }
}
