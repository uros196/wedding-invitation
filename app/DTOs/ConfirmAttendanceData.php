<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Http\Requests\ConfirmAttendanceRequest;

final readonly class ConfirmAttendanceData
{
    public function __construct(
        public array $confirmedGuestIds,
        public ?string $message = null,
        public ?array $plusOne = null,
    ) {}

    /**
     * Create a new instance from the given request.
     */
    public static function fromRequest(ConfirmAttendanceRequest $request): self
    {
        return new self(
            (array) $request->validated('confirmed_guest_ids', []),
            $request->validated('message'),
            $request->validated('plus_one'),
        );
    }
}
