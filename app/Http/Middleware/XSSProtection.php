<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that strips HTML tags from incoming request input to mitigate XSS.
 *
 * Can be configured to run only for specific HTTP methods and supports
 * conditions to skip protection entirely (e.g., for Livewire requests).
 */
class XSSProtection
{
    /**
     * If this is TRUE, XSS protection will be called only for selected methods.
     * Set this to FALSE if you want it to call on any methods.
     */
    protected bool $verify_methods = true;

    /**
     * Verify only selected methods.
     */
    protected array $methods = ['put', 'post'];

    /**
     * Condition to determine if XSS protection should be skipped.
     * Can be a boolean value or a Closure that returns a boolean.
     * Default is false, meaning protection is applied.
     */
    protected static bool|Closure $skip_protection_if = false;

    /**
     * The following method loops through all request inputs and strips out all tags from
     *  the request. This ensures that users are unable to set ANY HTML within the form
     *  submissions but also cleans up input.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkipProtection($request)) {
            return $next($request);
        }

        $input = $request->all();

        array_walk_recursive($input, function (&$input) {
            $input = $this->stripTags($input);
        });

        $request->merge($input);

        return $next($request);
    }

    /**
     * Removes HTML tags from the input if it's a string.
     * Non-string inputs are returned unchanged.
     */
    protected function stripTags(mixed $input): mixed
    {
        if (is_string($input)) {
            $input = strip_tags($input);
        }

        return $input;
    }

    /**
     * Sets a condition to determine when XSS protection should be skipped.
     */
    public static function skipProtectionIf(bool|Closure $condition): void
    {
        static::$skip_protection_if = $condition;
    }

    /**
     * Configures the middleware to skip XSS protection for Livewire requests.
     *
     * This method is used because Livewire requests contain special payload structures
     * with their own security mechanisms (like checksums). Applying XSS protection to
     * Livewire requests can interfere with Livewire's normal operation by stripping
     * necessary HTML tags from the payload that Livewire needs to function properly.
     * Livewire already implements its own security measures to prevent XSS attacks.
     */
    public static function skipForLivewireRequests(): void
    {
        static::skipProtectionIf(function (Request $request) {
            return app('livewire')->isLivewireRequest();
        });
    }

    /**
     * Determines whether XSS protection should be skipped for the given request.
     *
     * Protection is skipped if:
     * 1. The skip_protection_if property is set to true, or
     * 2. The skip_protection_if property is a Closure that returns true for the request or
     * 3. Method verification is enabled, and the request method is not in the list of methods to protect
     */
    protected function shouldSkipProtection(Request $request): bool
    {
        return
            (
                static::$skip_protection_if === true
                || (static::$skip_protection_if instanceof Closure && (static::$skip_protection_if)($request))
            )
            || ($this->verify_methods && !in_array(strtolower($request->method()), $this->methods));
    }
}
