<?php

declare(strict_types=1);

namespace App\Auth\FilamentAuth;

use App\Auth\ManagementUserProvider;
use App\Contracts\FilamentAuth;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Foundation\Application;

class ManagementDriver implements FilamentAuth
{
    /**
     * Get login auth provider class name.
     *
     * @return class-string<EloquentUserProvider>
     */
    public function authProvider(): string
    {
        return ManagementUserProvider::class;
    }

    /**
     * Create an authentication provider instance.
     */
    public function makeAuthProvider(Application $app, array $config): EloquentUserProvider
    {
        return new ($this->authProvider())($app['hash'], $config['model']);
    }

    /**
     * Get the provider name.
     */
    public function providerName(): string
    {
        return 'management';
    }

    /**
     * Get the provider's driver name.
     */
    public function driverName(): string
    {
        return 'management_driver';
    }
}
