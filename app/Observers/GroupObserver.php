<?php

declare(strict_types=1);

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
        if (blank($group->uuid)) {
            $group->uuid = (string) Str::uuid();
        }

        if (blank($group->wedding_id)) {
            $group->fill(['wedding_id' => auth()->user()->team->wedding->id]);
        }
    }
}
