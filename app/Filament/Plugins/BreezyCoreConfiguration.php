<?php

declare(strict_types=1);

namespace App\Filament\Plugins;

use App\Livewire\ProfilePersonalInfo;
use App\Livewire\ProfileUpdatePassword;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Validation\Rules\Password;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class BreezyCoreConfiguration
{
    /**
     * Pre-configures BreezyCore for Filament.
     */
    public static function make(): BreezyCore
    {
        return BreezyCore::make()
            ->myProfile(
                hasAvatars: true,
                userMenuLabel: __('Profile Settings'),
            )
            ->avatarUploadComponent(fn (): SpatieMediaLibraryFileUpload => SpatieMediaLibraryFileUpload::make('avatar_url')
                ->label(__('filament-breezy::default.fields.avatar'))
                ->avatar()
                ->collection('Avatar')
                ->conversion('preview')
                ->imageEditor()
                ->imageAspectRatio('1')
            )
            ->passwordUpdateRules(
                rules: [Password::default()]
            )
            ->enablePasskeys()
            ->myProfileComponents([
                'personal_info' => ProfilePersonalInfo::class,
                'update_password' => ProfileUpdatePassword::class,
            ]);
    }
}
