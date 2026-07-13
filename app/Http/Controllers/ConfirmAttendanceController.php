<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmAttendanceRequest;
use App\Models\Group;
use App\Services\GroupService;
use Illuminate\Http\RedirectResponse;

class ConfirmAttendanceController extends Controller
{
    public function __construct(
        protected GroupService $groupService
    ) {}

    public function __invoke(ConfirmAttendanceRequest $request, Group $group): RedirectResponse
    {
        $group->load('guests');
        $this->groupService->confirmAttendance($group, $request->toDto());

        return back()->with('success', __('messages.attendence_confirmation_success'));
    }
}
