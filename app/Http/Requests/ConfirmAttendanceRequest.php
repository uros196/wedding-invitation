<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\ConfirmAttendanceData;
use App\Models\Group;
use App\Rules\GuestInGroupRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmAttendanceRequest extends FormRequest
{
    /**
     * Determine if the request is authorized.
     */
    public function authorize(): bool
    {
        /** @var Group $group */
        $group = $this->route('group');

        return (bool) $group->wedding?->is_rsvp_open;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        /** @var Group $group */
        $group = $this->route('group');

        return [
            'confirmed_guest_ids' => ['array'],
            'confirmed_guest_ids.*' => ['exists:guests,id', new GuestInGroupRule($group)],
            'message' => ['nullable', 'string', 'max:5000'],
            'plus_one' => [
                Rule::when($group->has_plus_one, ['nullable', 'array']),
                Rule::when(! $group->has_plus_one, ['prohibited']),
            ],
            'plus_one.first_name' => ['required_with:plus_one', 'string', 'max:50'],
            'plus_one.last_name' => ['required_with:plus_one', 'string', 'max:50'],
        ];
    }

    /**
     * Convert the request to a DTO.
     */
    public function toDto(): ConfirmAttendanceData
    {
        return ConfirmAttendanceData::fromRequest($this);
    }
}
