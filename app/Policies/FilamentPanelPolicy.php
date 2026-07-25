<?php

namespace App\Policies;

use App\Enums\FilamentPanel;
use App\Models\User;

class FilamentPanelPolicy
{
    /**
     * Determines if the given user has access to the specified Filament panel.
     */
    public function accessPanel(User $user, FilamentPanel $panel): bool
    {
        return match ($panel) {
            // Check for Management panel privilege
            FilamentPanel::Management => $user->user_type->isManagementAdmin(),

            // Check for Wedding panel privilege
            FilamentPanel::Wedding => $user->user_type->isTeamMember()
                && filled($user->team_id)
                && $user->team->type->isWedding(),
        };
    }
}
