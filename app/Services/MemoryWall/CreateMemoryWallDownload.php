<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use App\Enums\MemoryWallDownloadStatus;
use App\Enums\MemoryWallUploadStatus;
use App\Jobs\PrepareMemoryWallDownloadJob;
use App\Models\MemoryWallDownload;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Creates an immutable snapshot of completed media and queues its archive.
 */
final readonly class CreateMemoryWallDownload
{
    /**
     * Inject the allocator used to keep duplicate archive names readable.
     */
    public function __construct(private ArchiveEntryNameAllocator $nameAllocator) {}

    /**
     * Snapshot completed media and queue archive creation after the transaction commits.
     *
     * @param  array<int, int>|null  $uploadIds
     */
    public function handle(Wedding $wedding, User $user, ?array $uploadIds = null): MemoryWallDownload
    {
        $download = DB::transaction(function () use ($wedding, $user, $uploadIds): MemoryWallDownload {
            $uuid = Str::uuid()->toString();
            $download = $wedding->memoryWallDownloads()->create([
                'user_id' => $user->getKey(),
                'uuid' => $uuid,
                'disk' => (string) config('memory-wall.archive_disk', config('memory-wall.media_disk', 's3')),
                'archive_path' => "memory-wall/archives/{$wedding->uuid}/{$uuid}.zip",
                'archive_name' => $this->archiveName(),
                'status' => MemoryWallDownloadStatus::Queued,
            ]);

            $this->snapshotCompletedMedia($wedding, $download, $uploadIds);
            $download->refresh();

            if ($download->total_files === 0) {
                throw new RuntimeException(__('wedding.memory_wall.download.no_files'));
            }

            return $download;
        });

        PrepareMemoryWallDownloadJob::dispatch($download)->afterCommit();

        return $download;
    }

    /**
     * Copy source metadata into immutable download items in bounded batches.
     *
     * @param  array<int, int>|null  $uploadIds
     */
    private function snapshotCompletedMedia(Wedding $wedding, MemoryWallDownload $download, ?array $uploadIds = null): void
    {
        $totalBytes = 0;
        $totalFiles = 0;

        $uploads = $wedding->memoryWallUploads()
            ->select(['id', 'media_id', 'original_name'])
            ->where('status', MemoryWallUploadStatus::Completed)
            ->whereNotNull('media_id')
            ->with('media')
            ->whereHas('media');

        if ($uploadIds !== null) {
            $uploads->whereKey($uploadIds);
        }

        $uploads
            ->chunkById(100, function (Collection $uploads) use ($download, &$totalBytes, &$totalFiles): void {
                foreach ($uploads as $upload) {
                    $media = $upload->media;
                    if ($media === null) {
                        continue;
                    }

                    $download->items()->create([
                        'memory_wall_upload_id' => $upload->getKey(),
                        'disk' => $media->disk,
                        'path' => $media->getPathRelativeToRoot(),
                        'original_name' => $upload->original_name,
                        'archive_name' => $this->nameAllocator->allocate($upload->original_name),
                        'size' => $media->size,
                        'mime_type' => $media->mime_type,
                    ]);
                    $totalBytes += $media->size;
                    $totalFiles++;
                }
            });

        $download->update([
            'total_bytes' => $totalBytes,
            'total_files' => $totalFiles,
        ]);
    }

    /**
     * Generate the user-visible archive filename.
     */
    private function archiveName(): string
    {
        return 'memory-wall-'.now()->format('Y-m-d-His').'.zip';
    }
}
