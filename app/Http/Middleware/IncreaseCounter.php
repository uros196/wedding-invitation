<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\HasCounts;
use Closure;
use Filament\Exceptions\NoDefaultPanelSetException;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property string $modelRouteKey
 */
class IncreaseCounter
{
    /**
     * Specify the route model to use for the middleware.
     */
    public static function using(string $routeModel): string
    {
        return static::class.':'.$routeModel;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws NoDefaultPanelSetException
     */
    public function handle(Request $request, Closure $next, ?string $routeModel = null): Response
    {
        if ($this->increaseCountAllowed($request)) {
            $this->model($request, $routeModel)->increaseCount();
        }

        return $next($request);
    }

    /**
     * Determines the route key for the model.
     *
     * This method checks if the 'modelRouteKey' property exists on the current instance.
     * If it exists, its value is returned as the route key.
     * If it does not exist, the method defaults to returning 'null'.
     */
    protected function modelRouteKey(): ?string
    {
        if (property_exists($this, 'modelRouteKey')) {
            return $this->modelRouteKey;
        }

        return null;
    }

    /**
     * Get the model instance for the given request.
     */
    protected function model(Request $request, ?string $routeModel): HasCounts
    {
        /** @var HasCounts */
        return $request->route($routeModel ?? $this->modelRouteKey());
    }

    /**
     * Check if counts are allowed to increase.
     *
     * @throws NoDefaultPanelSetException
     */
    protected function increaseCountAllowed(Request $request): bool
    {
        // If the request holds authenticated Filament user, skip the count increase.
        return ! ((bool) $request->user()?->canAccessPanel(Filament::getCurrentOrDefaultPanel()));
    }
}
