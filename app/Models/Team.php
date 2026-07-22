<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Represents a team of users who manage a wedding.
 */
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
    ];

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
