<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Archive;

use App\Contracts\MemoryWallArchiveSink;
use App\Contracts\MemoryWallArchiveStorage;
use Closure;

/**
 * Buffers ZIP bytes into S3-compatible multipart parts.
 */
final class MultipartArchiveSink implements MemoryWallArchiveSink
{
    private string $buffer = '';

    private int $nextPartNumber = 1;

    /**
     * @var array<int, array{part_number: int, etag: string}>
     */
    private array $parts = [];

    public function __construct(
        private readonly MemoryWallArchiveStorage $storage,
        private readonly string $path,
        private readonly string $uploadId,
        private readonly int $partSize,
        private readonly ?Closure $beforePart = null,
    ) {}

    /**
     * Buffer ZIP bytes until a complete S3 multipart part can be uploaded.
     */
    public function write(string $contents): void
    {
        while ($contents !== '') {
            $remaining = $this->partSize - strlen($this->buffer);
            $chunk = substr($contents, 0, $remaining);
            $this->buffer .= $chunk;
            $contents = substr($contents, strlen($chunk));

            if (strlen($this->buffer) === $this->partSize) {
                $this->flushPart();
            }
        }
    }

    /**
     * Upload the final partial part and return the ordered part list.
     *
     * @return array<int, array{part_number: int, etag: string}>
     */
    public function finish(): array
    {
        if ($this->buffer !== '') {
            $this->flushPart();
        }

        return $this->parts;
    }

    /**
     * Upload the current buffer and reset it for the next part.
     */
    private function flushPart(): void
    {
        $this->beforePart?->__invoke();

        $this->parts[] = [
            'part_number' => $this->nextPartNumber,
            'etag' => $this->storage->uploadPart(
                $this->path,
                $this->uploadId,
                $this->nextPartNumber,
                $this->buffer,
            ),
        ];
        $this->nextPartNumber++;
        $this->buffer = '';
    }
}
