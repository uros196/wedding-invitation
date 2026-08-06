<?php

declare(strict_types=1);

use App\Contracts\HasCounts;
use App\Enums\FilamentPanel;
use App\Http\Middleware\IncreaseCounter;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

final class TrackingCounter implements HasCounts
{
    public int $increaseCountCalls = 0;

    public function increaseCount(): void
    {
        $this->increaseCountCalls++;
    }
}

/**
 * Build a request with a route model and the authentication state needed by a test.
 *
 * @param  list<string>  $authenticatedGuards
 */
function makeIncreaseCounterRequest(
    HasCounts $model,
    string $routeKey = 'model',
    ?string $userAgent = 'Mozilla/5.0',
    array $authenticatedGuards = [],
): Request {
    $request = Request::create("/test/{$routeKey}");
    $route = new Route('GET', "/test/{{$routeKey}}", fn (): Response => new Response);
    $route->bind($request);
    $route->setParameter($routeKey, $model);

    $request->setRouteResolver(fn (): Route => $route);
    $request->setUserResolver(
        fn (?string $guard = null): ?object => in_array($guard, $authenticatedGuards, true)
            ? new stdClass
            : null,
    );

    if ($userAgent !== null) {
        $request->headers->set('User-Agent', $userAgent);
    }

    return $request;
}

test('increases the counter and returns the next response for a regular request', function (): void {
    $counter = new TrackingCounter;
    $request = makeIncreaseCounterRequest($counter, 'group');
    $middleware = new IncreaseCounter;
    $nextResponse = new Response('next response');
    $nextCalled = false;

    // A regular browser request is allowed to increase the route model counter.
    $response = $middleware->handle($request, function (Request $request) use ($nextResponse, &$nextCalled): Response {
        $nextCalled = true;

        return $nextResponse;
    }, 'group');

    // The model is incremented once, the request continues, and its response is preserved.
    expect($counter->increaseCountCalls)->toBe(1)
        ->and($nextCalled)->toBeTrue()
        ->and($response)->toBe($nextResponse);
});

test('uses the configured model route key when no route model argument is provided', function (): void {
    $counter = new TrackingCounter;
    $request = makeIncreaseCounterRequest($counter, 'group');
    $middleware = new class extends IncreaseCounter
    {
        public string $modelRouteKey = 'group';
    };

    // The middleware can resolve the route model from its configured property.
    $middleware->handle($request, fn (Request $request): Response => new Response);

    // The configured route key points to the expected counter model.
    expect($counter->increaseCountCalls)->toBe(1);
});

test('builds the middleware signature for a route model', function (): void {
    // The static helper produces the syntax Laravel uses when registering middleware parameters.
    expect(IncreaseCounter::using('group'))->toBe(IncreaseCounter::class.':group');
});

test('does not increase the counter for automated or missing user agents', function (?string $userAgent): void {
    $counter = new TrackingCounter;
    $request = makeIncreaseCounterRequest($counter, 'group', $userAgent);

    // Empty, missing, and known automated clients must not affect view counters.
    (new IncreaseCounter)->handle($request, fn (Request $request): Response => new Response, 'group');

    // The middleware still allows the request to continue without incrementing the model.
    expect($counter->increaseCountCalls)->toBe(0);
})->with([
    'missing user agent' => null,
    'empty user agent' => '',
    'crawler user agent' => 'Googlebot/2.1',
    'command line client' => 'curl/8.0',
]);

test('does not increase the counter for an authenticated Filament user', function (): void {
    $counter = new TrackingCounter;
    $request = makeIncreaseCounterRequest($counter, 'group', 'Mozilla/5.0', [FilamentPanel::Wedding->guard()]);

    // An authenticated user from any configured Filament guard is excluded from counting.
    (new IncreaseCounter)->handle($request, fn (Request $request): Response => new Response, 'group');

    // Authenticated panel activity must not increment the public counter.
    expect($counter->increaseCountCalls)->toBe(0);
});
