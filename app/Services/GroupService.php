<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConfirmAttendanceData;
use App\Enums\GuestStatus;
use App\Models\Group;
use App\Models\User;
use App\Notifications\AttendanceConfirmed;
use App\Notifications\NewMessageReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class GroupService
{
    /**
     * Check if the record exists (for field visibility).
     */
    public function isRecordExists(?Group $record): bool
    {
        return $record !== null;
    }

    /**
     * Get the most viewed groups.
     */
    public function getMostViewedGroups(int $limit = 5): Builder
    {
        return Group::query()->orderByDesc('views_count')->limit($limit);
    }

    /**
     * Confirm attendance for a group and send a message.
     */
    public function confirmAttendance(Group $group, ConfirmAttendanceData $data): void
    {
        $group->loadMissing('guests');

        $group->guests->each(function ($guest) use ($data) {
            $newStatus = in_array($guest->id, $data->confirmedGuestIds)
                ? GuestStatus::Confirmed
                : GuestStatus::Declined;

            $guest->update(['status' => $newStatus]);
        });

        $this->notifyAdminsAboutConfirmation($group, $data->confirmedGuestIds);

        if (filled($data->message)) {
            $message = $group->messages()->create([
                'content' => $data->message,
            ]);

            $this->notifyAdminsAboutMessage($group, $message);
        }
    }

    /**
     * Notify administrators about attendance confirmation.
     */
    protected function notifyAdminsAboutConfirmation(Group $group, array $confirmedGuestIds): void
    {
        $admins = User::all();

        Notification::send(
            $admins,
            new AttendanceConfirmed($group, count($confirmedGuestIds), $group->guests->count())
        );
    }

    /**
     * Notify administrators about a new message.
     */
    protected function notifyAdminsAboutMessage(Group $group, $message): void
    {
        $admins = User::all();

        Notification::send($admins, new NewMessageReceived($group, $message));
    }
}
