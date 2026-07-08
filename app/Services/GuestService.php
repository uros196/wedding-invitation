<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\GuestGroupedCountsData;
use App\DTOs\GuestStatsData;
use App\Models\Guest;

class GuestService
{
    /**
     * Retrieve guest status counts data.
     */
    public function getStatusCounts(): GuestStatsData
    {
        return GuestStatsData::make();
    }

    /**
     * Retrieve grouped counts of guests.
     */
    public function getGroupedCounts(): GuestGroupedCountsData
    {
        return GuestGroupedCountsData::make();
    }

    /**
     * Get a list of guests who can be companions.
     * Excludes the current guest and those who already have a parent.
     *
     * @return array<int, string>
     */
    public function getCompanionOptions(?Guest $record): array
    {
        return Guest::query()
            ->whereNull('parent_id')
            ->whereDoesntHave('companions')
            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
            ->get()
            ->mapWithKeys(fn (Guest $guest) => [$guest->id => $guest->full_name])
            ->toArray();
    }

    /**
     * Generate a label for an item in the companion repeater.
     */
    public function getCompanionItemLabel(array $state): ?string
    {
        $firstName = $state['first_name'] ?? '';
        $lastName = $state['last_name'] ?? '';

        return trim("{$firstName} {$lastName}") ?: null;
    }

    /**
     * Connect a list of companions with a parent guest.
     * Companions will automatically get the same group as the parent.
     *
     * @param  array<int>  $companionIds
     */
    public function syncCompanions(Guest $parent, array $companionIds): void
    {
        // Remove old companions (set parent_id to null)
        Guest::where('parent_id', $parent->id)
            ->whereNotIn('id', $companionIds)
            ->update(['parent_id' => null]);

        // Set new companions and assign them the same group
        if (! empty($companionIds)) {
            Guest::whereIn('id', $companionIds)
                ->update([
                    'parent_id' => $parent->id,
                    'group_id' => $parent->group_id,
                ]);
        }
    }

    /**
     * Synchronize the companion group with the parent group.
     * Called when the main guest's group changes.
     */
    public function syncCompanionsGroup(Guest $parent): void
    {
        if ($parent->group_id === null) {
            return;
        }

        $parent->companions()->update(['group_id' => $parent->group_id]);
    }

    /**
     * Assign a companion to a parent guest, setting the parent and group associations.
     */
    public function assignCompanionToParent(Guest|int $companion, Guest $parent): Guest
    {
        if (is_int($companion)) {
            $companion = Guest::find($companion);
        }

        $companion->parent_id = $parent->id;
        $companion->group_id = $parent->group_id;
        $companion->save();

        return $companion;
    }

    /**
     * Removes the parent-child relationship of a companion by setting its parent_id to null
     * and saving the updated record.
     */
    public function freeCompanionFromParent(Guest $companion): Guest
    {
        $companion->forceFill(['parent_id' => null])->save();

        return $companion;
    }

    /**
     * Create a new guest and immediately set them as a companion.
     */
    public function createCompanion(Guest $parent, array $data): Guest
    {
        return Guest::create(array_merge($data, [
            'parent_id' => $parent->id,
            'group_id' => $parent->group_id,
        ]));
    }

    /**
     * Retrieve counts of guests grouped by age.
     *
     * @return array<string, int>
     */
    public function getCountsByAge(): array
    {
        return Guest::query()
            ->selectRaw('age, count(*) as count')
            ->groupBy('age')
            ->pluck('count', 'age')
            ->toArray();
    }

    /**
     * Retrieve counts of guests grouped by gender.
     *
     * @return array<string, int>
     */
    public function getCountsByGender(): array
    {
        return Guest::query()
            ->selectRaw('gender, count(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();
    }
}
