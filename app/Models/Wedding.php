<?php

namespace App\Models;

use App\Support\Countdown;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Wedding extends Model implements HasMedia
{
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
        'schedules',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'wedding_date' => 'date',
        'rsvp_deadline' => 'datetime',
        'schedules' => 'array',
    ];

    /**
     * Register the media collections for the wedding.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->singleFile();
        $this->addMediaCollection('meta_image')->singleFile();
    }

    /**
     * Get the URL of the image used for meta tags.
     * Falls back to the hero image when no dedicated meta image is set.
     */
    public function getMetaImageUrl(): ?string
    {
        return $this->getFirstMediaUrl('meta_image')
            ?: ($this->getFirstMediaUrl('hero') ?: null);
    }

    /**
     * Get the wedding countdown.
     */
    public function weddingCountdown(): Attribute
    {
        return Attribute::make(
            get: fn () => filled($this) ? new Countdown($this->wedding_date)
                ->setFormat(config('wedding.widgets.countdown.wedding_format')) : null,
        );
    }

    /**
     * Get the RSVP countdown.
     */
    public function rsvpCountdown(): Attribute
    {
        return Attribute::make(
            get: fn () => filled($this) ? new Countdown($this->rsvp_deadline)
                ->setFormat(config('wedding.widgets.countdown.rsvp_format')) : null,
        );
    }
}
