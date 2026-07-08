<?php

namespace App\Observers;

use App\Models\Guest;
use App\Services\GuestService;

class GuestObserver
{
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
