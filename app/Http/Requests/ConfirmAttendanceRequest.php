<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\ConfirmAttendanceData;
use App\Models\Group;
use App\Rules\GuestInGroupRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmAttendanceRequest extends FormRequest
{
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
