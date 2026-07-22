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
        // Replicate 'has_memory_wall' option to team's wedding.
        if (filled($team->has_memory_wall) && filled($team->wedding)) {
            $team->wedding->forceFill(['has_memory_wall' => $team->has_memory_wall])->save();
        }
    }
}
