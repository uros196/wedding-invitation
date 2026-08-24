<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Scopes\PublishedScope;
use Illuminate\Database\Eloquent\Builder;

trait IsPublished
{
    /**
     * Register the published global scope.
     */
    public static function bootIsPublished(): void
    {
        static::addGlobalScope(new PublishedScope);
    }

    /**
     * Remove the published global scope from a query.
     */
    public function scopeWithoutPublish(Builder $query): Builder
    {
        return $query->withoutGlobalScope(PublishedScope::class);
    }
}
