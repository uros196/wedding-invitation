<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\WeddingObserver;
use App\Policies\WeddingPolicy;
use App\Services\MemoryWallService;
use App\Support\Countdown;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy(WeddingObserver::class)]
#[UsePolicy(WeddingPolicy::class)]
class Wedding extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'bride_name',
        'groom_name',
        'wedding_date',
        'rsvp_deadline',
        'welcome_text',
        'has_memory_wall',
        'memory_wall_open_until',
        'meta_title',
        'meta_description',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'wedding_date' => 'date',
            'rsvp_deadline' => 'datetime',
            'has_memory_wall' => 'boolean',
            'memory_wall_open_until' => 'datetime',
        ];
    }

    /**
     * Get the timeline entries for the wedding.
     */
    public function timelines(): HasMany
    {
        return $this->hasMany(WeddingTimeline::class)->orderBy('sort_order');
    }

    /**
     * Get related groups for the wedding.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get the related guests for the wedding.
     */
    public function guests(): HasManyThrough
    {
        return $this->hasManyThrough(Guest::class, Group::class);
    }

    /**
     * Get the team managing the wedding.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get users assigned to the wedding's team.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'team_id', 'team_id');
    }

    /**
     * Get the wedding countdown.
     */
    protected function weddingCountdown(): Attribute
    {
        return Attribute::make(
            get: fn () => filled($this) ? new Countdown($this->wedding_date)
                ->setFormat(config('wedding.widgets.countdown.wedding_format')) : null,
        );
    }

    /**
     * Get the RSVP countdown.
     */
    protected function rsvpCountdown(): Attribute
    {
        return Attribute::make(
            get: fn () => filled($this) ? new Countdown($this->rsvp_deadline)
                ->setFormat(config('wedding.widgets.countdown.rsvp_format')) : null,
        );
    }

    /**
     * Determine if the RSVP is currently open based on the RSVP deadline.
     */
    protected function isRsvpOpen(): Attribute
    {
        return Attribute::get(fn () => $this->rsvp_deadline->isFuture());
    }

    /**
     * Get the attribute for the countdown due date and time.
     */
    protected function countdownDueDatetime(): Attribute
    {
        return Attribute::get(function () {
            $time = $this->timelines()->visible()->first()?->time;

            if ($time) {
                return $this->wedding_date->setTimeFrom($time);
            }

            return $this->wedding_date->startOfDay();
        });
    }

    /**
     * Determine if the memory wall form is currently open.
     */
    protected function isMemoryWallFormOpen(): Attribute
    {
        return Attribute::get(fn () => resolve(MemoryWallService::class)->isFormOpen($this));
    }

    /**
     * Define an accessor to retrieve the URL for the memory wall of the wedding.
     */
    protected function memoryWallUrl(): Attribute
    {
        return Attribute::get(fn () => $this->exists
            ? route('memory-wall.show', $this)
            : null
        );
    }

    /**
     * Register the media collections for the wedding.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->singleFile();
        $this->addMediaCollection('meta_image')->singleFile();

        // Declare the MemoryWall media collection
        $this->addMediaCollection('MemoryWall')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->fit(Fit::Contain, 368, 235);
            });
    }

    /**
     * Get the URL of the hero image displayed on the invitation.
     */
    public function getHeroImageUrl(): string
    {
        return $this->getFirstMediaUrl('hero');
    }

    /**
     * Get the URL of the image used for meta tags.
     * Falls back to the hero image when no dedicated meta-image is set.
     */
    public function getMetaImageUrl(): string
    {
        return $this->getFirstMediaUrl('meta_image')
            ?: $this->getHeroImageUrl();
    }

    /**
     * Load the default relationships for the model.
     */
    public function loadDefaultRelationships(): self
    {
        return $this->load([
            'timelines' => function (Builder $query) {
                $query->visible();
            },
        ]);
    }
}
