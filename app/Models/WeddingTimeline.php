<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WeddingTimeline extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wedding_id',
        'title',
        'address',
        'time',
        'map_url',
        'is_visible',
        'icon',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'time' => 'datetime:H:i',
        ];
    }

    /**
     * Get the wedding that owns the timeline.
     */
    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Get the groups that have hidden this timeline item.
     */
    public function hiddenByGroups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_hidden_timeline_items');
    }

    /**
     * Scope a query to only include visible records.
     */
    public function scopeVisible(Builder $builder, bool $visible = true): void
    {
        $builder->where('is_visible', $visible);
    }

    /**
     * Get the formatted list name attribute combining time and title.
     */
    protected function listName(): Attribute
    {
        return Attribute::get(fn () => "{$this->time->format('H:i')} - {$this->title}");
    }
}
