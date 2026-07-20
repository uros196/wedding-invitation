<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Represents common image aspect ratios used in the application.
 */
enum AspectRatio: string implements HasLabel
{
    case Square = '1:1';
    case Meta = '1.91:1';
    case Wide = '16:9';
    case Portrait = '4:5';
    case Tall = '9:16';

    /**
     * Gets the label for the aspect ratio.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Square => '1:1',
            self::Meta => '1.91:1 (Google / Social)',
            self::Wide => '16:9',
            self::Portrait => '4:5',
            self::Tall => '9:16',
        };
    }

    /**
     * Recommended aspect ratios for meta tag images (Open Graph / Google).
     * Google and Meta (Facebook) recommend 1.91:1 (1200x630).
     */
    public static function forMeta(): array
    {
        return [
            self::Square,
            self::Meta,
        ];
    }

    /**
     * Recommended aspect ratios for the invitation hero image.
     */
    public static function forHero(): array
    {
        return [
            self::Tall,
            self::Portrait,
        ];
    }
}
