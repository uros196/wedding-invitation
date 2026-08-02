<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\FilamentPanel;
use App\Filament\Wedding\Plugins\EchoRegisterPlugin;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

class WeddingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id(FilamentPanel::Wedding->id())
            ->path(FilamentPanel::Wedding->path())
            ->authGuard(FilamentPanel::Wedding->guard())
            ->login()
            ->spa()
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
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugins([
                EchoRegisterPlugin::make(),
                FilamentUnsavedChangesModalPlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->shouldRegisterNavigation(false)
                    ->shouldShowEmailForm(false)
                    ->shouldShowLocaleForm(true, [
                        'sr_Latn' => 'Srpski',
                        'en' => 'English',
                    ])
                    ->shouldShowAvatarForm(
                        directory: 'avatars',
                        //only accept jpeg and png files with a maximum size of 2MB
                        rules: 'mimes:jpeg,png|max:2048',
                    ),
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => Blade::render('@livewire(\'global-export\')'),
            )
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
            ])
            ->userMenuItems([
                'profile' => Action::make('profile_settings')
                    ->label(fn ():string => __('filament-edit-profile::default.title'))
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon(Heroicon::Cog6Tooth)
            ]);
    }
}
