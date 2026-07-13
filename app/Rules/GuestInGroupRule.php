<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Group;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class GuestInGroupRule implements ValidationRule
{
    public function __construct(protected Group $group) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->group->guests()->where('id', $value)->exists()) {
            $fail(__('The selected guest does not belong to this group.'));
        }
    }
}
