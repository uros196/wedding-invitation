<?php

namespace App\Models;

use App\Support\Countdown;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Wedding extends Model
{
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
