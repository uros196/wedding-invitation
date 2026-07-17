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
     *
     * @return array<string, mixed>
     */
    public function getWeddingData(Wedding $wedding): array
    {
        return $wedding->load('timelines')->attributesToArray();
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
