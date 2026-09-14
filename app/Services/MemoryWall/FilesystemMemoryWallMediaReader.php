<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Opens original media streams from the disk captured in the download snapshot.
 */
final class FilesystemMemoryWallMediaReader
{
    /**
     * Open a read-only stream using the disk and path stored in the snapshot.
     *
     * @return resource
     */
    public function open(string $disk, string $path)
    {
        $stream = Storage::disk($disk)->readStream($path);

        if (! is_resource($stream)) {
            throw new RuntimeException("Unable to open memory wall media [{$path}].");
        }

        return $stream;
    }
}
