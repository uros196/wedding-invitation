<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\InvitationStatsData;
use App\Enums\Status;
use App\Models\Group;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTimeline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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
                'groups as sent_invitations_count' => fn (Builder $query): Builder => $query->sent(),
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
     * Resolve the published wedding associated with a user's team, if it exists.
     */
    public function getWeddingForUser(User $user): ?Wedding
    {
        return $user->team()->with('wedding')->first()?->wedding;
    }

    /**
     * Resolve the wedding associated with a user's team regardless of its status.
     */
    public function getWeddingForUserWithoutPublish(User $user): ?Wedding
    {
        $team = $user->team()->first();

        return $team?->wedding()->withoutPublish()->first();
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
        $wedding ??= $this->getWeddingForUserWithoutPublish($user) ?? Wedding::make([
            'status' => Status::Draft,
        ]);
        $wedding->fill(Arr::except($data, ['status', 'team_id', 'uuid']));

        $team = $user->team()->firstOrFail();

        if ($wedding->team_id !== $team->id) {
            $wedding->team()->associate($team);
        }

        $wedding->save();

        return $wedding;
    }

    /**
     * Publish a wedding after confirming ownership.
     */
    public function publishWedding(Wedding $wedding, User $user): Wedding
    {
        $ownedWedding = $this->getWeddingForUserWithoutPublish($user);

        abort_unless($ownedWedding?->is($wedding), 403);
        abort_unless($wedding->isReadyToPublish(), 422);

        $wedding->update(['status' => Status::Published]);

        return $wedding->refresh();
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
