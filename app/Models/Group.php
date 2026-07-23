<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasCounts;
use App\Models\Concerns\Countable;
use App\Observers\GroupObserver;
use App\Policies\GroupPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(GroupObserver::class)]
#[UsePolicy(GroupPolicy::class)]

class Group extends Model implements HasCounts, HasMedia
{
    use Countable;
    use HasFactory;
    use InteractsWithMedia;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wedding_id',
        'uuid',
        'name',
        'is_sent',
        'has_plus_one',
        'views_count',
        'invitation_title',
        'invitation_message',
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
     * Get related wedding.
     */
    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Get the timeline items that are hidden for this group.
     */
    public function hiddenTimelineItems(): BelongsToMany
    {
        return $this->belongsToMany(WeddingTimeline::class, 'group_hidden_timeline_items')
            ->where('group_hidden_timeline_items.wedding_id', $this->wedding_id)
            ->withPivot('wedding_id')
            ->withTimestamps();
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
     * Scope a query to filter by sent status.
     */
    public function scopeSent(Builder $query, bool $sent = true): void
    {
        $query->where('is_sent', $sent);
    }

    /**
     * Define an attribute for generating the URL of the group.
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('group.show', $this));
    }

    /**
     * Register the media collections for the group.
     */
    public function registerMediaCollections(): void
    {
        // Declare the MetaImage media collection
        $this->addMediaCollection('MetaImage')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('preview')
                    ->fit(Fit::Contain, 600, 600)
                    ->format('webp');
            });
    }

    /**
     * Get the URL of the image used for meta tags, if any.
     */
    public function getMetaImageUrl(string $conversion = ''): ?string
    {
        return $this->getFirstMediaUrl('MetaImage', $conversion) ?: null;
    }

    /**
     * Determine if the related wedding exists.
     */
    public function hasWedding(): bool
    {
        return $this->wedding()->exists();
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
