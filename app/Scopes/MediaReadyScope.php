<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Enums\MediaStatus;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hides Media rows until their backing object has reached the final path.
 *
 * @implements Scope<Media>
 */
final class MediaReadyScope implements Scope
{
    /**
     * @param  Builder<covariant Media>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(
            $model->qualifyColumn('status'),
            MediaStatus::Ready->value,
        );
    }
}
