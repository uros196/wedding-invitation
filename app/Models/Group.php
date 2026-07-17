<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\GroupObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(GroupObserver::class)]
class Group extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'is_sent',
        'has_plus_one',
        'views_count',
        'meta_title',
        'meta_description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_sent' => 'boolean',
            'has_plus_one' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    /**
     * Get the timeline items that are hidden for this group.
     */
    public function hiddenTimelineItems(): BelongsToMany
    {
        return $this->belongsToMany(WeddingTimeline::class, 'group_hidden_timeline_items');
    }

    /**
     * Get the guests for the group.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    /**
     * Get the messages for the group.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Define an attribute for generating the URL of the group.
     */
    public function url(): Attribute
    {
        return Attribute::get(fn () => route('group.show', $this));
    }

    /**
     * Register the media collections for the group.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('meta_image')->singleFile();
    }

    /**
     * Get the URL of the image used for meta tags, if any.
     */
    public function getMetaImageUrl(): ?string
    {
        return $this->getFirstMediaUrl('meta_image') ?: null;
    }

    /**
     * Determine if the model has any meta-information.
     */
    public function hasAnyMeta(): bool
    {
        return filled($this->meta_title)
            || filled($this->meta_description)
            || $this->getMetaImageUrl();
    }

    /**
     * Determine if the group has only one guest.
     */
    public function hasOnlyOneGuest(): bool
    {
        return $this->guests()->count() === 1;
    }
}
