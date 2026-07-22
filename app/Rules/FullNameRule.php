<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class FullNameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match(
            '/^\p{L}[\p{L}\p{M}]*(?:[\x20\x27\x{2019}-]\p{L}[\p{L}\p{M}]*)*$/u',
            $value,
        ) !== 1) {
            $fail('validation.full_name')->translate();
        }
    }
}
