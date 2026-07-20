<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Countdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
        'bride_name',
        'groom_name',
        'wedding_date',
        'rsvp_deadline',
        'welcome_text',
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
     * Register the media collections for the wedding.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->singleFile();
        $this->addMediaCollection('meta_image')->singleFile();
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
