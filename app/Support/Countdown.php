<?php

namespace App\Support;

use App\Services\DateService;
use Carbon\CarbonImmutable;
use DateInterval;
use Illuminate\Support\Carbon;

/**
 * Countdown class for calculating and formatting time differences.
 *
 * This readonly class provides utilities for checking if a date has passed,
 * formatting time differences, and working with Carbon dates.
 */
class Countdown
{
    protected DateService $dateService;

    protected string $dateFormat = 'd.m.Y H:i';

    /**
     * Create a new Countdown instance.
     */
    public function __construct(public readonly Carbon|CarbonImmutable $date)
    {
        $this->dateService = resolve(DateService::class);
    }

    /**
     * Check if the countdown date is in the past.
     */
    public function isPast(): bool
    {
        return (bool) $this->diff()->invert;
    }

    /**
     * Get the formatted countdown value as a string.
     */
    public function value(): string
    {
        return $this->date->diffForHumans();
    }

    /**
     * Set the format for displaying the countdown value.
     */
    public function setFormat(string $format): self
    {
        $this->dateFormat = $format;

        return $this;
    }

    /**
     * Format the countdown date using the specified format.
     */
    public function datetime(?string $format = null): string
    {
        return $this->date->format($format ?? $this->dateFormat);
    }

    /**
     * Calculate the date interval difference between now and the countdown date.
     */
    protected function diff(): DateInterval
    {
        return $this->dateService->diff($this->date);
    }
}
