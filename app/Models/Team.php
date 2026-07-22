<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\TeamObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(TeamObserver::class)]
class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'has_memory_wall',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'has_memory_wall' => 'boolean',
        ];
    }

    /**
     * Get the related wedding.
     */
    public function wedding(): HasOne
    {
        return $this->hasOne(Wedding::class);
    }

    /**
     * Get the related users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
