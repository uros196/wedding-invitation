<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\InvitationStatsData;
use App\Models\Wedding;

class WeddingService
{
    /**
     * Get invitation statistics.
     */
    public function getInvitationStats(): InvitationStatsData
    {
        return InvitationStatsData::make();
    }

    /**
     * Get the single wedding record, creating it if it does not exist yet.
     */
    public function getWedding(): Wedding
    {
        return Wedding::firstOrCreate([]);
    }

    /**
     * Get wedding data for filling the management form.
     * Applies default schedules when none are set.
     *
     * @return array<string, mixed>
     */
    public function getWeddingData(Wedding $wedding): array
    {
        $data = $wedding->attributesToArray();

        if (blank($data['schedules'] ?? null)) {
            $data['schedules'] = config('wedding.schedules');
        }

        return $data;
    }

    /**
     * Save or update wedding data.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveWeddingData(Wedding $wedding, array $data): void
    {
        $wedding->update($data);
    }
}
