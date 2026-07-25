<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\User;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Defines the types of users that can access the Filament panels.
 */
enum UserType: string implements HasLabel
{
    case ManagementAdmin = 'management_admin';
    case TeamMember = 'team_member';

    /**
     * Get the label.
     */
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ManagementAdmin => 'Management Admin',
            self::TeamMember => 'Team Member',
        };
    }

    /**
     * Determines the guard based on the current context and user information provided.
     */
    public function guard(User $user): string
    {
        return match ($this) {
            self::ManagementAdmin => 'management',
            self::TeamMember => $user->team->type->guard(),
        };
    }

    /**
     * Determine if the current instance represents a Management Admin.
     */
    public function isManagementAdmin(): bool
    {
        return $this === self::ManagementAdmin;
    }

    /**
     * Determine if the current instance represents a Team Member.
     */
    public function isTeamMember(): bool
    {
        return $this === self::TeamMember;
    }
}
