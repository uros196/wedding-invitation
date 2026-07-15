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

        $confirmedIds = $data->confirmedGuestIds;

        if ($group->has_plus_one && filled($data->plusOne)) {
            $parentGuest = $group->guests->first();

            if ($parentGuest) {
                $plusOneGuest = $group->guests()->create([
                    'parent_id' => $parentGuest->id,
                    'first_name' => $data->plusOne['first_name'],
                    'last_name' => $data->plusOne['last_name'],
                    'status' => GuestStatus::Confirmed,
                ]);

                $group->update(['has_plus_one' => false]);

                // Add the new guest to the confirmed list so it doesn't get declined in the next step
                $confirmedIds[] = $plusOneGuest->id;

                // Refresh guests after adding the plus one
                $group->load('guests');
            }
        }

        $group->guests->each(function ($guest) use ($confirmedIds) {
            $newStatus = in_array($guest->id, $confirmedIds)
                ? GuestStatus::Confirmed
                : GuestStatus::Declined;

            $guest->update(['status' => $newStatus]);
        });

        $this->notifyAdminsAboutConfirmation($group, $confirmedIds);

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
