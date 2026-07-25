<?php

declare(strict_types=1);

namespace App\Auth;

use App\Enums\UserType;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ManagementUserProvider extends EloquentUserProvider
{
    /**
     * Get a new query builder for the model instance.
     *
     * Authenticated users must have a member record.
     * That is a valid front end user.
     *
     * @template TModel of Model
     *
     * @param  TModel|null  $model
     * @return Builder<TModel>
     */
    protected function newModelQuery($model = null): Builder
    {
        return parent::newModelQuery($model)
            ->where('user_type', UserType::ManagementAdmin);
    }
}
