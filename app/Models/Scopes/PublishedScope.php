<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class PublishedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(
            $model->qualifyColumn('status'),
            Status::Published->value,
        );
    }
}
