<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /**
     * The attributes that are mass-assignable.
     *
     * @returns array
     */
    protected $fillable = [
        'group_id',
        'content',
    ];

    /**
     * Get related group.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
