<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

/**
 * Adds the global asynchronous memory wall download manager to the panel.
 */
final class MemoryWallDownloadPlugin implements Plugin
{
    /**
     * Create the panel plugin using Filament's fluent registration style.
     */
    public static function make(): static
    {
        return new self;
    }

    /**
     * Return the stable identifier used by the Filament panel.
     */
    public function getId(): string
    {
        return 'filament-wedding-memory-wall-downloads';
    }

    /**
     * Render the global manager after the panel body so it can overlay any page.
     */
    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render("@livewire('memory-wall-download-manager')"),
        );
    }

    /**
     * The manager has no additional boot-time configuration.
     */
    public function boot(Panel $panel): void {}
}
