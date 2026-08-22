<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConfirmAttendanceData;
use App\Enums\GuestStatus;
use App\Events\AttendanceConfirmed;
use App\Events\MessageReceived;
use App\Models\Group;
use App\Models\User;
use App\Models\WeddingTimeline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
     * Save the groups that can see a timeline item.
     *
     * Groups not included in the submitted list are stored as hidden for the
     * timeline item. The submitted group IDs are restricted to groups that
     * belong to the timeline item's wedding before the pivot is synchronized.
     */
    public function saveVisibility(?WeddingTimeline $timeline, mixed $visibleGroupIds): void
    {
        if (! $timeline) {
            return;
        }

        $allGroupIds = Group::query()
            ->where('wedding_id', $timeline->wedding_id)
            ->pluck('id')
            ->map(static fn (mixed $groupId): int => (int) $groupId)
            ->all();
        $visibleGroupIds = collect(is_array($visibleGroupIds) ? $visibleGroupIds : [])
            ->map(static fn (mixed $groupId): int => (int) $groupId)
            ->intersect($allGroupIds)
            ->values()
            ->all();
        $hiddenGroupIds = array_values(array_diff($allGroupIds, $visibleGroupIds));

        $timeline->hiddenByGroups()->syncWithPivotValues($hiddenGroupIds, [
            'wedding_id' => $timeline->wedding_id,
        ]);
        $timeline->unsetRelation('hiddenByGroups');
    }

    /**
     * Confirm attendance for a group and send a message.
     */
    public function confirmAttendance(Group $group, ConfirmAttendanceData $data): void
    {
        $group->loadMissing('guests', 'team');

        $confirmedIds = $data->confirmedGuestIds;
        $attendanceChanged = false;

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
                $attendanceChanged = true;

                // Add the new guest to the confirmed list so it doesn't get declined in the next step
                $confirmedIds[] = $plusOneGuest->id;

                // Refresh guests after adding the plus one
                $group->load('guests');
            }
        }

        $group->guests->each(function ($guest) use ($confirmedIds, &$attendanceChanged) {
            $newStatus = in_array($guest->id, $confirmedIds)
                ? GuestStatus::Confirmed
                : GuestStatus::Declined;

            if ($guest->status !== $newStatus) {
                $attendanceChanged = true;
                $guest->update(['status' => $newStatus]);
            }
        });

        if ($attendanceChanged) {
            AttendanceConfirmed::dispatch($group, $confirmedIds);
        }

        if (filled($data->message)) {
            $message = $group->messages()->create([
                'content' => $data->message,
            ]);

            MessageReceived::dispatch($message, $group);
        }
    }
}
