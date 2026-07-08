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
     * Get wedding data.
     * Returns default values if data does not exist.
     */
    public function getWeddingData(): array
    {
        $wedding = Wedding::first();

        if ($wedding) {
            return $wedding->toArray();
        }

        return [
            'schedules' => config('wedding.schedules'),
        ];
    }

    /**
     * Save or update wedding data.
     */
    public function saveWeddingData(array $data): void
    {
        Wedding::updateOrCreate([], $data);
    }
}
