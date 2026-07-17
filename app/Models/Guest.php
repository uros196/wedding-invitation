<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Age;
use App\Enums\Gender;
use App\Enums\GuestStatus;
use App\Observers\GuestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(GuestObserver::class)]
class Guest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'parent_id',
        'first_name',
        'last_name',
        'status',
        'age',
        'gender',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GuestStatus::class,
            'age' => Age::class,
            'gender' => Gender::class,
        ];
    }

    /**
     * Get the group that the guest belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the parent guest.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'parent_id');
    }

    /**
     * Get the companions for this guest.
     */
    public function companions(): HasMany
    {
        return $this->hasMany(Guest::class, 'parent_id');
    }

    /**
     * Scope a query to only include guests with a confirmed status.
     */
    public function scopeConfirmed(Builder $query): void
    {
        $query->where('status', GuestStatus::Confirmed);
    }

    /**
     * Get the guest's full name.
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Get the age label of the guest.
     */
    public function ageLabel(): Attribute
    {
        return Attribute::get(fn () => $this->age?->getLabel());
    }

    /**
     * Get the label for the guest's gender.
     */
    public function genderLabel(): Attribute
    {
        return Attribute::get(fn () => $this->gender?->getLabel());
    }

    /**
     * Determine if the status is accepted.
     */
    public function isAccepted(): Attribute
    {
        return Attribute::get(fn () => $this->status->isAccepted());
    }

    /**
     * Determine if the entity has companions.
     */
    public function hasCompanions(): bool
    {
        return $this->companions()->count() > 0;
    }
}
