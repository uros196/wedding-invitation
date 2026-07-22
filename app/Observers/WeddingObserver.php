<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Wedding;
use Illuminate\Support\Str;

class WeddingObserver
{
    /**
     * Handle the Group "creating" event.
     */
    public function creating(Wedding $wedding): void
    {
        if (blank($wedding->uuid)) {
            $wedding->forceFill(['uuid' => (string) Str::uuid()]);
        }
    }
}
