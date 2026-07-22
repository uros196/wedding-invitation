<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\InvitationStatsData;
use App\Models\Group;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTimeline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Provides wedding data for public and authenticated application flows.
 */
class WeddingService
{
    /**
     * Get invitation statistics.
     */
    public function getInvitationStats(User $user): InvitationStatsData
    {
        $wedding = $this->getWeddingForUser($user)
            ?->loadSum('groups', 'views_count')
            ?->loadCount([
                'groups as sent_invitations_count' => fn (Builder $query): Builder => $query
                    ->where('is_sent', true),
            ]);

        return InvitationStatsData::make($wedding);
    }

    /**
     * Resolve the wedding associated with a guest group.
     */
    public function getWeddingForGroup(Group $group): Wedding
    {
        return $group->wedding()->firstOrFail();
    }

    /**
     * Resolve the wedding associated with a user's team, if it exists.
     */
    public function getWeddingForUser(User $user): ?Wedding
    {
        return $user->loadMissing('team.wedding')->team?->wedding;
    }

    /**
     * Get wedding data for filling the management form.
     */
    public function getWeddingData(?Wedding $wedding): array
    {
        return $wedding?->load('timelines')->attributesToArray() ?? [];
    }

    /**
     * Save or update wedding data.
     */
    public function saveWeddingData(?Wedding $wedding, User $user, array $data): Wedding
    {
        $wedding ??= Wedding::make();
        $wedding->fill($data);

        $team = $user->team()->firstOrFail();

        if ($wedding->team_id !== $team->id) {
            $wedding->team()->associate($team);
        }

        $wedding->save();

        return $wedding;
    }

    /**
     * Retrieves a collection of visible timelines associated with the given wedding.
     *
     * @return Collection<WeddingTimeline>
     */
    public function timelineList(?Wedding $wedding): Collection
    {
        return $wedding?->timelines()->visible()->get() ?? collect();
    }

}
