<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnlockMemoryWallShareRequest extends FormRequest
{
    /**
     * Public share visitors may submit the password for the bound link.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validate only the password input needed by the unlock action.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Retrieve the validated password as a string.
     */
    public function password(): string
    {
        return (string) $this->validated('password');
    }
}
