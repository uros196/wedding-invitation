<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Group;
use App\Models\Guest;
use App\Services\GuestService;

class GuestObserver
{
    /**
     * Handle the Guest "creating" event.
     */
    public function creating(Guest $guest): void
    {
        if (filled($guest->team_id)) {
            return;
        }

        // Fill team_id from group_id if available
        if (filled($guest->group_id)) {
            $group = Group::with('wedding:id,team_id')->find($guest->group_id);
            $guest->fill(['team_id' => $group->wedding->team_id]);

            return;
        }

        // Otherwise, fill team_id from the authenticated user
        $user = auth()->user();
        $guest->fill(['team_id' => $user->team_id]);
    }

    /**
     * Handle the Guest "saved" event.
     */
    public function saved(Guest $guest): void
    {
        if ($guest->isDirty('group_id') && $guest->group_id !== null) {
            app(GuestService::class)->syncCompanionsGroup($guest);
        }
    }
}
