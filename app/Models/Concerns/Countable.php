<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait Countable
{
    /**
     * Increase the view counts.
     */
    public function increaseCount(): void
    {
        $this->increment($this->viewCountColumn());
    }

    /**
     * Get the fully qualified column name for view counts.
     */
    protected function viewCountQualifyColumn(): string
    {
        return $this->qualifyColumn($this->viewCountColumn());
    }

    /**
     * Get the database column name used for view counts.
     */
    protected function viewCountColumn(): string
    {
        return 'views_count';
    }
}
