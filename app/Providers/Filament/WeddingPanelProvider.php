<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\FilamentPanel;
use App\Filament\Plugins\BreezyCoreConfiguration;
use App\Filament\Wedding\Pages\Dashboard;
use App\Filament\Wedding\Plugins\EchoRegisterPlugin;
use App\Filament\Wedding\Plugins\ExportPlugin;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WeddingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return FilamentPanel::Wedding->configurePanel($panel)
            ->default()
            ->login()
            ->revealablePasswords()
            ->spa()
            ->viteTheme('resources/css/filament/wedding/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->discoverResources(in: app_path('Filament/Wedding/Resources'), for: 'App\Filament\Wedding\Resources')
            ->discoverPages(in: app_path('Filament/Wedding/Pages'), for: 'App\Filament\Wedding\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Wedding/Widgets'), for: 'App\Filament\Wedding\Widgets')
            ->plugins([
                EchoRegisterPlugin::make(),
                ExportPlugin::make(),
                FilamentUnsavedChangesModalPlugin::make(),
                BreezyCoreConfiguration::make(),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
