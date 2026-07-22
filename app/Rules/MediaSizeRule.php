<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

class MediaSizeRule implements ValidationRule
{
    public function __construct(protected array $limits) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $mime = $value->getMimeType();
        $sizeInBytes = $value->getSize();

        foreach ($this->limits as $pattern => $limitString) {
            if ($this->matchesPattern($mime, $pattern)) {
                $maxBytes = $this->parseSize($limitString);

                if ($sizeInBytes > $maxBytes) {
                    $fail('validation.media_size')
                        ->translate([
                            'limit' => $limitString,
                            'type' => $this->getFriendlyTypeName($mime),
                        ]);
                    return;
                }
            }
        }
    }

    /**
     * Checks if a given mime type matches a specified pattern.
     */
    protected function matchesPattern(string $mime, string $pattern): bool
    {
        if ($pattern === '*' || $pattern === $mime) {
            return true;
        }

        if (str_contains($pattern, '*')) {
            $regex = str_replace(['/', '*'], ['\/', '.*'], $pattern);
            return (bool) preg_match('/^' . $regex . '$/', $mime);
        }

        return false;
    }

    /**
     * Parses a size string and converts it into its corresponding size in bytes.
     *
     * @param string $sizeString The size string (e.g., "10MB", "500KB") to be parsed.
     * @return int The size in bytes as an integer.
     */
    protected function parseSize(string $sizeString): int
    {
        $units = ['B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4];
        $number = (float) $sizeString;

        $unit = str_replace((string)$number, '', $sizeString)
                |> trim(...)
                |> strtoupper(...);

        if (empty($unit)) {
            $unit = 'KB'; // Default to KB like Laravel's max rule
        }

        if (! isset($units[$unit])) {
            return (int) $number;
        }

        return (int) ($number * pow(1024, $units[$unit]));
    }

    /**
     * Retrieves a user-friendly name for a given mime type.
     */
    protected function getFriendlyTypeName(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return __('validation.media_types.image');
        }

        if (str_starts_with($mime, 'video/')) {
            return __('validation.media_types.video');
        }

        return __('validation.media_types.file');
    }
}
