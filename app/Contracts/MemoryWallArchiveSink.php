<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Receives generated ZIP bytes and exposes the completed multipart parts.
 */
interface MemoryWallArchiveSink
{
    /**
     * Append bytes to the sink's in-memory part buffer.
     */
    public function write(string $contents): void;

    /**
     * Flush the final partial part and return all uploaded part metadata.
     *
     * @return array<int, array{part_number: int, etag: string}>
     */
    public function finish(): array;
}
