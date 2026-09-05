<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\MediaStatus;
use App\Models\Media;
use App\Scopes\MediaReadyScope;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasReady
{
    /**
     * Register the global scope that hides incomplete records.
     */
    public static function bootHasReady(): void
    {
        static::addGlobalScope(new MediaReadyScope);
    }

    /**
     * Remove the ready global scope from a query.
     *
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    #[Scope]
    protected function withoutReady(Builder $query): Builder
    {
        return $query->withoutGlobalScope(MediaReadyScope::class);
    }

    /**
     * Restrict a query to records that are still pending.
     *
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    #[Scope]
    protected function onlyPending(Builder $query): Builder
    {
        return $query
            ->withoutGlobalScope(MediaReadyScope::class)
            ->where($query->getModel()->qualifyColumn('status'), MediaStatus::Pending->value);
    }

    /**
     * Restrict a query to ready records without applying other ready scopes.
     *
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    #[Scope]
    protected function withReady(Builder $query): Builder
    {
        return $query
            ->withoutGlobalScope(MediaReadyScope::class)
            ->where($query->getModel()->qualifyColumn('status'), MediaStatus::Ready->value);
    }

    /**
     * Restrict a query to ready records while retaining the global scope.
     *
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    #[Scope]
    protected function ready(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('status'), MediaStatus::Ready->value);
    }

    /**
     * Mark a directly uploaded media object as ready for regular queries.
     */
    public function markAsReady(): void
    {
        $this->forceFill(['status' => MediaStatus::Ready])->save();
    }

    /**
     * Keep a pre-created media object hidden while its upload is incomplete.
     */
    public function markAsPending(): void
    {
        $this->forceFill(['status' => MediaStatus::Pending])->save();
    }
}
