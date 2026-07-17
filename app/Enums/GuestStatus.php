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
            self::Pending => __('Pending'),
            self::Confirmed => __('Confirmed'),
            self::Declined => __('Declined'),
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

    /**
     * Determines if the current instance represents a confirmed state.
     */
    public function isAccepted(): bool
    {
        return $this === self::Confirmed;
    }
}
