<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Foundation\Application;

interface FilamentAuth
{
    /**
     * Get login auth provider class name.
     *
     * @return class-string<EloquentUserProvider>
     */
    public function authProvider(): string;

    /**
     * Create an authentication provider instance.
     */
    public function makeAuthProvider(Application $app, array $config): EloquentUserProvider;

    /**
     * Get the provider name.
     */
    public function providerName(): string;

    /**
     * Get the provider's driver name.
     */
    public function driverName(): string;
}
