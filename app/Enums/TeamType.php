<?php

declare(strict_types=1);

namespace App\Enums;

use App\Auth\FilamentAuth\TeamDriver;
use App\Contracts\FilamentAuth;
use App\Models\Team;
use Filament\Support\Contracts\HasLabel;

enum TeamType: string implements HasLabel
{
    case Wedding = 'wedding';

    /**
     * Gets the label for the option.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Wedding => 'Wedding',
        };
    }

    /**
     * Get login guard.
     */
    public function guard(): string
    {
        return match ($this) {
            self::Wedding => 'wedding',
        };
    }

    /**
     * Get TeamDriver needed to configure Filament Auth.
     */
    public function filamentAuthDriver(): FilamentAuth
    {
        return new TeamDriver($this);
    }

    /**
     * Get broadcast channel name.
     */
    public function broadcastChannelName(?Team $team = null): string
    {
        return 'team'. ucfirst($this->name). (blank($team) ? '.{team}' : ".{$team->getKey()}");
    }

    /**
     * Determine if the current instance represents a wedding event.
     */
    public function isWedding(): bool
    {
        return $this === self::Wedding;
    }
}
