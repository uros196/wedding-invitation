<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Enums\MemoryWallUploadStatus;
use App\Models\MemoryWallUpload;
use App\Models\User;
use App\Models\Wedding;
use App\Notifications\MemoryWallUploadDigest;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Sends and marks one quiet-period group of completed Memory Wall uploads.
 */
final class SendMemoryWallUploadDigest
{
    /**
     * Select, notify, and mark the same upload group within one transaction.
     *
     * The calling job holds the wedding lock. Older scheduled jobs re-read the
     * pending group and wait if its newest completion is still inside the quiet
     * period. Row locks and synchronous notification delivery keep database
     * notifications and digest timestamps together when a transaction retries.
     */
    public function handle(int $weddingId): void
    {
        $cutoff = now()->subMinutes((int) config('memory-wall.digest_delay_minutes', 10));

        DB::transaction(function () use ($weddingId, $cutoff): void {
            $uploads = $this->pendingUploads($weddingId);

            if (! $this->quietPeriodHasEnded($uploads, $cutoff)) {
                return;
            }

            $users = $this->recipients($weddingId);

            if ($users->isEmpty()) {
                return;
            }

            Notification::sendNow($users, $this->makeNotification($uploads));

            $this->markAsSent($uploads);
        });
    }

    /**
     * Lock pending completed uploads in completion order for the transaction.
     *
     * @return Collection<int, MemoryWallUpload>
     */
    private function pendingUploads(int $weddingId): Collection
    {
        return MemoryWallUpload::query()
            ->where('wedding_id', $weddingId)
            ->where('status', MemoryWallUploadStatus::Completed)
            ->whereNull('digest_sent_at')
            ->whereNotNull('completed_at')
            ->lockForUpdate()
            ->orderBy('completed_at')
            ->get();
    }

    /**
     * Wait for the latest completion, not just the oldest pending upload.
     *
     * @param  Collection<int, MemoryWallUpload>  $uploads
     */
    private function quietPeriodHasEnded(Collection $uploads, CarbonInterface $cutoff): bool
    {
        $latestUpload = $uploads->last();

        return $latestUpload !== null && ! $latestUpload->completed_at->isAfter($cutoff);
    }

    /**
     * Find the wedding users, leaving uploads pending when none are available.
     *
     * @return Collection<int, User>
     */
    private function recipients(int $weddingId): Collection
    {
        $wedding = Wedding::query()->find($weddingId);

        if ($wedding === null) {
            return new Collection;
        }

        return $wedding->users()->get();
    }

    /**
     * Summarize the media types in the selected upload group.
     *
     * @param  Collection<int, MemoryWallUpload>  $uploads
     */
    private function makeNotification(Collection $uploads): MemoryWallUploadDigest
    {
        $imageCount = $uploads
            ->filter(fn (MemoryWallUpload $upload): bool => Str::startsWith($upload->mime_type, 'image/'))
            ->count();
        $videoCount = $uploads
            ->filter(fn (MemoryWallUpload $upload): bool => Str::startsWith($upload->mime_type, 'video/'))
            ->count();

        return new MemoryWallUploadDigest($imageCount, $videoCount);
    }

    /**
     * Mark only the uploads included in the successfully sent notification.
     *
     * @param  Collection<int, MemoryWallUpload>  $uploads
     */
    private function markAsSent(Collection $uploads): void
    {
        MemoryWallUpload::query()
            ->whereKey($uploads->modelKeys())
            ->update(['digest_sent_at' => now()]);
    }
}
