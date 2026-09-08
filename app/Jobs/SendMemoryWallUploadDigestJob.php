<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\MemoryWall\SendMemoryWallUploadDigest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends one notification for all completed Memory Wall uploads after a quiet period.
 */
final class SendMemoryWallUploadDigestJob implements ShouldQueue
{
    use Queueable;

    /**
     * Maximum number of attempts for transient database or notification failures.
     */
    public int $tries = 3;

    /**
     * This job only performs short database and notification operations.
     */
    public int $timeout = 60;

    /**
     * Create a digest job for one wedding.
     */
    public function __construct(public int $weddingId) {}

    /**
     * Retry transient database or notification failures with increasing delays.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Serialize competing digest jobs for the same wedding across workers.
     */
    public function handle(SendMemoryWallUploadDigest $sendDigest): void
    {
        $lock = Cache::lock($this->lockKey(), 60);

        if (! $lock->get()) {
            return;
        }

        try {
            $sendDigest->handle($this->weddingId);
        } finally {
            $lock->release();
        }
    }

    /**
     * Log a failed digest so the pending uploads remain available for a retry.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Memory Wall upload digest failed.', [
            'wedding_id' => $this->weddingId,
            'exception' => $exception?->getMessage(),
        ]);
    }

    /**
     * Use one distributed lock for all digest jobs belonging to this wedding.
     */
    private function lockKey(): string
    {
        return "memory-wall-upload-digest:{$this->weddingId}";
    }
}
