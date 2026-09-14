<?php

namespace App\Http\Controllers;

use App\Contracts\MemoryWallArchiveStorage;
use App\Contracts\MemoryWallMediaUrl;
use App\Models\MemoryWallDownload;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;

/**
 * Authorizes memory wall downloads before redirecting the browser to storage.
 */
final class MemoryWallDownloadController extends Controller
{
    public function __construct(
        private readonly MemoryWallMediaUrl $mediaUrl,
        private readonly MemoryWallArchiveStorage $archiveStorage,
    ) {}

    /**
     * Redirect a completed upload to a temporary URL for its original media.
     */
    public function single(Wedding $wedding, MemoryWallUpload $memoryWallUpload): RedirectResponse
    {
        $this->authorizeWedding($wedding);
        abort_unless($memoryWallUpload->status->isCompleted() && $memoryWallUpload->media !== null, 404);

        return redirect()->away($this->mediaUrl->make($memoryWallUpload->media, $memoryWallUpload->original_name));
    }

    /**
     * Redirect a ready archive to its temporary storage URL.
     */
    public function archive(Wedding $wedding, MemoryWallDownload $memoryWallDownload): RedirectResponse
    {
        $this->authorizeWedding($wedding);
        $expiresAt = $memoryWallDownload->expires_at;
        $archivePath = (string) $memoryWallDownload->archive_path;

        if (! $memoryWallDownload->status->isReady() || blank($archivePath) || $expiresAt === null || $expiresAt->isPast()) {
            abort(410);
        }

        return redirect()->away($this->archiveStorage->temporaryUrl(
            $archivePath,
            $expiresAt,
            $memoryWallDownload->archive_name,
        ));
    }

    /**
     * Ensure the authenticated wedding user belongs to the requested wedding.
     */
    private function authorizeWedding(Wedding $wedding): void
    {
        abort_unless($wedding->team_id === auth('wedding')->user()?->team_id, 403);
    }
}
