<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';

    /**
     * Determine whether the media can be returned by regular queries.
     */
    public function isReady(): bool
    {
        return $this === self::Ready;
    }
}
