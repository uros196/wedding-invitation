<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserWeddingGuestScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->forUser($this->user());
    }

    /**
     * Extend the query builder with the necessary functions.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('forUser', function (Builder $builder, ?User $user = null) {
            if (! $this->needApply($user)) {
                return $builder;
            }

            return $builder->whereHas('team', function (Builder $builder) use ($user) {
                $builder->whereKey($user->team_id);
            });

            // return $builder->whereHas('group.wedding', function (Builder $query) use ($user): void {
            //     $query->whereHas('users', function (Builder $query) use ($user) {
            //         $query->whereKey($user->getKey());
            //     });
            // });
        });
    }

    /**
     * Determine if the scope needs to be applied.
     */
    protected function needApply(?User $user): bool
    {
        return filled($user);
    }

    /**
     * Retrieve the authenticated user.
     */
    protected function user(): ?User
    {
        return auth()->user();
    }
}
