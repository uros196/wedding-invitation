<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FilamentPanel;
use App\Enums\UserType;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['name', 'email', 'password', 'user_type', 'team_id', 'locale'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]

class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia
{
    use HasFactory, Notifiable;
    use InteractsWithMedia;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
        ];
    }

    /**
     * Determine if the current user can access the specified panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return FilamentPanel::tryFrom($panel->getId())?->canAccess($this) ?? false;
    }

    /**
     * Get the related Team.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the avatar URL for the user.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('Avatar', 'preview');
    }

    /**
     * Register the user's media collections and conversions.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('Avatar')
            ->singleFile()
            ->registerMediaConversions(function (): void {
                $this->addMediaConversion('preview')
                    ->fit(Fit::Crop, 500, 500)
                    ->format('webp');
            });
    }

    /**
     * Determine if the team has a wedding associated.
     */
    public function hasWedding(): bool
    {
        return $this->team()
            ->whereHas('wedding', fn (Builder $query): Builder => $query->withoutPublish())
            ->exists();
    }

    /**
     * Determine whether the current team has a published wedding.
     */
    public function hasPublishedWedding(): bool
    {
        return $this->team()->whereHas('wedding')->exists();
    }
}
