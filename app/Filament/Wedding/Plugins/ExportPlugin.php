<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class ExportPlugin implements Plugin
{
    public static function make(): static
    {
        return new static;
    }

    /**
     * Get the ID of the plugin.
     */
    public function getId(): string
    {
        return 'filament-wedding-export';
    }

    /**
     * Register the export menu in the panel's top bar.
     */
    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn (): string => Blade::render('@livewire(\'global-export\')'),
        );
    }

    public function boot(Panel $panel): void {}
}
