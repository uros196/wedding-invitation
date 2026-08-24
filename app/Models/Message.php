<?php

declare(strict_types=1);

namespace App\Models;

use App\Policies\MessagePolicy;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(MessagePolicy::class)]
class Message extends Model
{
    /**
     * @use HasFactory<MessageFactory>
     */
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group_id',
        'content',
    ];

    /**
     * Get related group.
     *
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
