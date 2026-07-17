<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ChronologicalOrderRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        $times = array_column($value, 'time');
        $times = array_filter($times);

        for ($i = 1; $i < count($times); $i++) {
            if ($times[$i] <= $times[$i - 1]) {
                $fail('validation.chronological_order')->translate();

                return;
            }
        }
    }
}
