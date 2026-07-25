<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\FilamentPanel;
use App\Policies\FilamentPanelPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->filamentConfig();
    }

    /**
     * Configure Filament.
     */
    protected function filamentConfig(): void
    {
        // Every Filament panel has its own auth providers.
        // Here we're registering all of them.
        FilamentPanel::registerAuthProviders();

        // Map the FilamentPanelPolicy with FilamentPanel to determine
        // if the user has access to the panel.
        Gate::policy(FilamentPanel::class, FilamentPanelPolicy::class);
    }
}
