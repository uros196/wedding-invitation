<?php

declare(strict_types=1);

namespace App\Enums;

use App\Auth\FilamentAuth\ManagementDriver;
use App\Contracts\FilamentAuth;
use App\Models\User;
use Closure;
use Filament\Panel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

enum FilamentPanel: string
{
    // ATTENTION! If you add a new panel, make sure to update the FilamentPanelPolicy and TeamType enum.
    case Management = 'management';
    case Wedding = 'wedding';

    /**
     * Build the configuration for the auth guards.
     */
    public static function buildGuardsConfig(): Collection
    {
        return self::mapCases(fn (self $panel) => [
            $panel->guard() => [
                'driver' => $panel->guardDriver(),
                'provider' => $panel->authDriver()->providerName(),
            ],
        ]);
    }

    /**
     * Build the configuration for the auth providers.
     */
    public static function buildProvidersConfig(): Collection
    {
        return self::mapCases(fn (self $panel) => [
            $panel->authDriver()->providerName() => [
                'driver' => $panel->authDriver()->driverName(),
                'model' => User::class,
            ],
        ]);
    }

    /**
     * Register the authentication providers.
     */
    public static function registerAuthProviders(): void
    {
        collect(self::cases())->each(function (self $panel) {
            $auth = $panel->authDriver();

            Auth::provider(
                $auth->driverName(),
                fn ($app, array $config) => $auth->makeAuthProvider($app, $config)
            );
        });
    }

    /**
     * Get all available guards.
     */
    public static function guards(): array
    {
        return collect(self::cases())->map(fn (self $panel) => $panel->guard())->toArray();
    }

    /**
     * Configure the panel with the appropriate settings.
     */
    public function configurePanel(Panel $panel): Panel
    {
        return $panel
            ->id($this->id())
            ->path($this->path())
            ->authGuard($this->guard());
    }

    /**
     * Get the Filament panel ID.
     */
    public function id(): string
    {
        return $this->value;
    }

    /**
     * Get the Filament panel URL path.
     */
    public function path(): string
    {
        return match ($this) {
            self::Management => 'management',
            self::Wedding => 'admin',
        };
    }

    /**
     * Get the Filament panel authentication guard.
     */
    public function guard(): string
    {
        return match ($this) {
            self::Management => 'management',
            self::Wedding => TeamType::Wedding->guard(),
        };
    }

    /**
     * Get the guard driver name.
     */
    public function guardDriver(): string
    {
        return 'session';
    }

    /**
     * Get login auth provider class name.
     */
    public function authDriver(): FilamentAuth
    {
        return match ($this) {
            self::Management => new ManagementDriver,
            self::Wedding => TeamType::Wedding->filamentAuthDriver(),
        };
    }

    /**
     * Determine if the given user can access the panel.
     */
    public function canAccess(User $user): bool
    {
        return $user->can('access-panel', $this);
    }

    /**
     * Map and transform cases using the given closure.
     */
    protected static function mapCases(Closure $closure): Collection
    {
        return collect(self::cases())->mapWithKeys($closure);
    }
}
