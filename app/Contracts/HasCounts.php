<?php

declare(strict_types=1);

namespace App\Contracts;

interface HasCounts
{
    /**
     * Increase the view counts.
     */
    public function increaseCount(): void;
}
