<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum NavigationGroup implements HasLabel
{
    case Wedding;
    case Guests;

    /**
     * Get the label for the navigation group.
     */
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Wedding => __('Wedding'),
            self::Guests => __('Guests'),
        };
    }
}
