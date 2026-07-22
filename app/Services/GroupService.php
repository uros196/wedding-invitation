<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConfirmAttendanceData;
use App\Enums\GuestStatus;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTimeline;
use App\Notifications\AttendanceConfirmed;
use App\Notifications\NewMessageReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class GroupService
{
    public function __construct(protected WeddingService $weddingService) {}

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
    public function getMostViewedGroups(User $user, int $limit = 5): Builder
    {
        $user->loadMissing('team.wedding');
        $weddingId = $user->team?->wedding?->id;

        return Group::query()
            ->when(
                $weddingId,
                fn (Builder $query): Builder => $query->where('wedding_id', $weddingId),
                fn (Builder $query): Builder => $query->whereKey(-1),
            )
            ->orderByDesc('views_count')
            ->limit($limit);
    }

    /**
     * Retrieve the available timeline for a given group.
     * Any timeline items that are marked as hidden for the group will be excluded from the result.
     *
     * @return Collection<int, WeddingTimeline>
     */
    public function getAvailableTimeline(?Group $group): Collection
    {
        $timeline = $this->weddingService->timelineList($group?->wedding);

        if (blank($timeline)) {
            return collect();
        }

        $hiddenIds = $group->hiddenTimelineItems()->pluck('wedding_timeline_id')->toArray();

        return $timeline->reject(fn (WeddingTimeline $item) => in_array($item->id, $hiddenIds));
    }

    /**
     * Sync the timeline for a group by updating hidden timeline items.
     *
     * This method determines which timeline items should be hidden for the given group
     * by comparing the currently visible timeline items with the provided state.
     * The hidden timeline items are then synchronized with the group.
     */
    public function syncTimeline(Group $group, ?array $state): void
    {
        $allVisibleIds = $this->weddingService->timelineList($group->wedding)->pluck('id')->toArray();
        $hiddenIds = array_values(array_diff($allVisibleIds, $state ?? []));

        $group->hiddenTimelineItems()->syncWithPivotValues($hiddenIds, [
            'wedding_id' => $group->wedding_id,
        ]);
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
                [$firstName, $lastName] = array_pad(explode(' ', $data->plusOne['full_name'], 2), 2, '');

                $plusOneGuest = $group->guests()->create([
                    'parent_id' => $parentGuest->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
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
        $admins = $this->getWeddingUsers($group->wedding);

        Notification::send(
            $admins,
            new AttendanceConfirmed($group, count($confirmedGuestIds), $group->guests->count())
        );
    }

    /**
     * Notify administrators about a new message.
     */
    protected function notifyAdminsAboutMessage(Group $group, Message $message): void
    {
        $admins = $this->getWeddingUsers($group->wedding);

        Notification::send($admins, new NewMessageReceived($group, $message));
    }

    /**
     * Get users assigned to teams for the group's wedding.
     *
     * @return Collection<int, User>
     */
    protected function getWeddingUsers(?Wedding $wedding): Collection
    {
        if (! $wedding) {
            return collect();
        }

        return $wedding->users()->get();
    }
}
