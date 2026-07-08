<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Represents the attendance status of a guest.
 */
enum GuestStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Declined = 'declined';

    /**
     * Gets the label for the guest status.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Na čekanju',
            self::Confirmed => 'Potvrđeno',
            self::Declined => 'Odbijeno',
        };
    }

    /**
     * Retrieves the color associated with the current status.
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Confirmed => 'success',
            self::Declined => 'danger',
        };
    }
}
