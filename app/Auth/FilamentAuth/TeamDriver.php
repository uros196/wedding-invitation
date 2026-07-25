<?php

declare(strict_types=1);

namespace App\Auth\FilamentAuth;

use App\Auth\TeamUserProvider;
use App\Contracts\FilamentAuth;
use App\Enums\TeamType;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Foundation\Application;

class TeamDriver implements FilamentAuth
{
    public function __construct(protected TeamType $teamType) {}

    /**
     * Get login auth provider class name.
     *
     * @return class-string<EloquentUserProvider>
     */
    public function authProvider(): string
    {
        return TeamUserProvider::class;
    }

    /**
     * Create an authentication provider instance.
     */
    public function makeAuthProvider(Application $app, array $config): EloquentUserProvider
    {
        return new ($this->authProvider())($app['hash'], $config['model'])
            ->usingType($this->teamType->value);
    }

    /**
     * Get the provider name.
     */
    public function providerName(): string
    {
        return "team_provider_{$this->suffix()}";
    }

    /**
     * Get the provider's driver name.
     */
    public function driverName(): string
    {
        return "team_driver_{$this->suffix()}";
    }

    /**
     * Get team suffix.
     */
    protected function suffix(): string
    {
        return $this->teamType->value;
    }
}
