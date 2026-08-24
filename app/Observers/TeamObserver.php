<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Team;

class TeamObserver
{
    /**
     * Handle the Guest "saving" event.
     */
    public function saving(Team $team): void
    {
        if (filled($team->has_memory_wall)) {
            $wedding = $team->wedding()->withoutPublish()->first();

            if ($wedding !== null) {
                $wedding->forceFill(['has_memory_wall' => $team->has_memory_wall])->save();
            }
        }
    }
}
