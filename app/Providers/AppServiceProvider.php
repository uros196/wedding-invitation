<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\MemoryWallMultipartStorage;
use App\Services\MemoryWall\S3MultipartUploadStorage;
use App\Support\MetaFactory;
use BezhanSalleh\LanguageSwitch\Enums\TriggerStyle;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use BladeUI\Icons\Factory as IconFactory;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Keep the upload workflow independent of the concrete S3 SDK adapter.
        $this->app->bind(MemoryWallMultipartStorage::class, S3MultipartUploadStorage::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Turn off wrapping of JSON resources into 'data'.
        JsonResource::withoutWrapping();

        // Create MetaFactory singleton.
        $this->app->singleton(MetaFactory::class, function () {
            return new MetaFactory;
        });

        // Define Filament language switcher.
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(['sr_Latn', 'en'])
                ->userPreferredLocale(auth()->user()?->locale ?? config('app.locale'))
                ->trigger(style: TriggerStyle::Avatar);
        });

        // Expand SVG factory with a new path
        $this->callAfterResolving(IconFactory::class, function (IconFactory $factory) {
            $factory->add('default', [
                'path' => resource_path('svg'),
                'prefix' => '',
            ]);
        });

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
