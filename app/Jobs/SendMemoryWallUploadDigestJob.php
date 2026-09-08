<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MemoryWallUploadStatus;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Notifications\MemoryWallUploadDigest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
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
    public function handle(): void
    {
        $lock = Cache::lock($this->lockKey(), 60);

        if (! $lock->get()) {
            return;
        }

        try {
            $this->sendPendingDigest();
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
     * Find and mark one complete quiet-period group atomically with its notification.
     *
     * Every completion schedules a job for the end of the quiet period. Older
     * jobs are intentionally not cancelled: this method re-reads all pending
     * uploads and exits when the newest one is still inside that period. The
     * newest scheduled job can therefore act as the trailing-edge debounce,
     * while the wedding lock prevents concurrent workers from sending the same
     * digest. The row lock and transaction keep selecting and marking the same
     * upload group together, so a retry cannot include an already-notified
     * upload twice.
     */
    private function sendPendingDigest(): void
    {
        $cutoff = now()->subMinutes((int) config('memory-wall.digest_delay_minutes', 10));

        DB::transaction(function () use ($cutoff): void {
            $uploads = MemoryWallUpload::query()
                ->where('wedding_id', $this->weddingId)
                ->where('status', MemoryWallUploadStatus::Completed)
                ->whereNull('digest_sent_at')
                ->whereNotNull('completed_at')
                ->lockForUpdate()
                ->orderBy('completed_at')
                ->get();

            if ($uploads->isEmpty()) {
                return;
            }

            $latestUpload = $uploads->last();

            if ($latestUpload->completed_at->isAfter($cutoff)) {
                return;
            }

            $wedding = Wedding::query()->find($this->weddingId);

            if ($wedding === null) {
                return;
            }

            $users = $wedding->users()->get();

            if ($users->isEmpty()) {
                return;
            }

            $imageCount = $uploads
                ->filter(fn (MemoryWallUpload $upload): bool => Str::startsWith($upload->mime_type, 'image/'))
                ->count();
            $videoCount = $uploads
                ->filter(fn (MemoryWallUpload $upload): bool => Str::startsWith($upload->mime_type, 'video/'))
                ->count();

            Notification::sendNow(
                $users,
                new MemoryWallUploadDigest($imageCount, $videoCount),
            );

            MemoryWallUpload::query()
                ->whereKey($uploads->modelKeys())
                ->update(['digest_sent_at' => now()]);
        });
    }

    /**
     * Use one distributed lock for all digest jobs belonging to this wedding.
     */
    private function lockKey(): string
    {
        return "memory-wall-upload-digest:{$this->weddingId}";
    }
}
