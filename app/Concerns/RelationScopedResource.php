<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for scoping Filament resources to specific relationships.
 *
 * This trait provides functionality to filter Eloquent queries based on
 * relationships, ensuring resources only display records related to a
 * specific parent model.
 */
trait RelationScopedResource
{
    /**
     * Get the name of the relationship to scope the resource by.
     *
     * @throws \Exception When not implemented in the using class
     */
    protected static function getScopeRelation(): string
    {
        throw new \Exception('RelationScopedResource::getScopeRelation() must be implemented');
    }

    /**
     * Get the key of the related model to scope by.
     *
     * @throws \Exception When not implemented in the using class
     */
    protected static function getRelatedKey(): string|int|null
    {
        throw new \Exception('RelationScopedResource::getRelatedKey() must be implemented');
    }

    /**
     * Build the scoped query for the relationship.
     */
    protected static function scopedRelationBuilder(Builder $query, string|int $relationKey): Builder
    {
        return $query->whereKey($relationKey);
    }

    /**
     * Get the Eloquent query builder scoped to the relationship.
     *
     * Returns a query builder that filters records based on the configured
     * relationship and related key. If no related key is found, returns
     * a query that yields no results.
     *
     * @throws \Exception
     */
    public static function getEloquentQuery(): Builder
    {
        $relationKey = self::getRelatedKey();

        if (blank($relationKey)) {
            return parent::getEloquentQuery()->whereKey(-1);
        }

        return parent::getEloquentQuery()
            ->whereHas(
                static::getScopeRelation(),
                fn (Builder $query): Builder => self::scopedRelationBuilder($query, $relationKey),
            );
    }
}
