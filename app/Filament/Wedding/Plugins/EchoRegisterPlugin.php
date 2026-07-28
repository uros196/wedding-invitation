<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;

class EchoRegisterPlugin implements Plugin
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
        return 'filament-wedding-echo-register';
    }

    /**
     * Registers a panel and attaches a render hook for rendering
     * a specific view at the end of the body section.
     *
     * This view will register listeners for the team channel and
     * call for some specific actions. If you need to add more
     * listeners, you can modify the view file.
     */
    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::BODY_END,
            fn (): Htmlable => view('filament.wedding.echo-register', [
                'user' => auth()->user(),
            ])
        );
    }

    public function boot(Panel $panel): void {}
}
