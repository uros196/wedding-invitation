<?php

declare(strict_types=1);

namespace App\Models;

use App\Policies\MemoryWallSharePolicy;
use Carbon\CarbonInterface;
use Database\Factories\MemoryWallShareFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[UsePolicy(MemoryWallSharePolicy::class)]
class MemoryWallShare extends Model
{
    /** @use HasFactory<MemoryWallShareFactory> */
    use HasFactory;

    /**
     * The attributes that can be assigned when a share is managed by its wedding.
     *
     * @var list<string>
     */
    protected $fillable = [
        'wedding_id',
        'name',
        'password',
        'expires_at',
        'allow_downloads',
    ];

    /**
     * Keep the password hash out of every serialized representation.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Mirror database defaults on unsaved model instances.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'allow_downloads' => false,
    ];

    /**
     * Cast access settings to their application types and hash passwords on assignment.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'expires_at' => 'datetime',
            'allow_downloads' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $share): void {
            $share->uuid ??= self::newUuid();
        });
    }

    /**
     * Get the wedding that owns the share link.
     *
     * @return BelongsTo<Wedding, $this>
     */
    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Scope links that have no expiry or have not reached it yet.
     */
    #[Scope]
    protected function active(Builder $query, ?CarbonInterface $at = null): void
    {
        $at ??= now();

        $query->where(function (Builder $query) use ($at): void {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', $at);
        });
    }

    /**
     * Use the public UUID for share route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Generate a public identifier for a new share link.
     */
    public static function newUuid(): string
    {
        return Str::uuid()->toString();
    }
}
