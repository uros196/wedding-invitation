<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Archive;

use App\Contracts\MemoryWallArchiveSink;
use Closure;
use RuntimeException;
use Throwable;

/**
 * Writes ZIP64 entries directly to a byte sink without creating a local file.
 * Entries use the stored method so source bytes are copied only once.
 */
final class StreamingZipWriter
{
    private const ZIP64_VERSION = 45;

    private const UTF8_FLAG = 0x0800;

    private const DATA_DESCRIPTOR_FLAG = 0x0008;

    private int $offset = 0;

    /**
     * @var array<int, array{name: string, crc: int, size: int, offset: int, time: int, date: int}>
     */
    private array $entries = [];

    /**
     * Create a writer that sends ZIP bytes to the supplied multipart sink.
     */
    public function __construct(private readonly MemoryWallArchiveSink $sink) {}

    /**
     * Copy one source stream into a ZIP64 entry while calculating its checksum.
     *
     * The data descriptor allows the local header to be written before the
     * source stream has been read, so the source does not need to be buffered.
     *
     * @param  Closure(): mixed  $streamFactory
     * @param  Closure(int): void|null  $onBytes
     */
    public function addFile(
        string $name,
        Closure $streamFactory,
        int $expectedSize,
        ?Closure $onBytes = null,
        ?Closure $beforeRead = null,
    ): void {
        $name = $this->normalizeName($name);
        $nameBytes = $name;
        [$time, $date] = $this->dosDateTime();
        $entryOffset = $this->offset;

        $this->write(pack(
            'VvvvvvVVVvv',
            0x04034B50,
            self::ZIP64_VERSION,
            self::UTF8_FLAG | self::DATA_DESCRIPTOR_FLAG,
            0,
            $time,
            $date,
            0,
            0,
            0,
            strlen($nameBytes),
            0,
        ));
        $this->write($nameBytes);

        $stream = $streamFactory();
        if (! is_resource($stream)) {
            throw new RuntimeException("Unable to open archive source [{$name}].");
        }

        $hash = hash_init('crc32b');
        $size = 0;

        try {
            while (! feof($stream)) {
                $beforeRead?->__invoke();
                $contents = fread($stream, 1024 * 1024);
                if ($contents === false) {
                    throw new RuntimeException("Unable to read archive source [{$name}].");
                }

                if ($contents === '') {
                    continue;
                }

                hash_update($hash, $contents);
                $size += strlen($contents);
                $this->write($contents);
                $onBytes?->__invoke(strlen($contents));
            }
        } catch (Throwable $exception) {
            fclose($stream);

            throw $exception;
        }

        fclose($stream);

        if ($size !== $expectedSize) {
            throw new RuntimeException(
                "Archive source [{$name}] changed from {$expectedSize} to {$size} bytes.",
            );
        }

        $crc = (int) hexdec(hash_final($hash));
        $this->write(pack('VV', 0x08074B50, $crc).$this->packUint64($size).$this->packUint64($size));

        $this->entries[] = [
            'name' => $nameBytes,
            'crc' => $crc,
            'size' => $size,
            'offset' => $entryOffset,
            'time' => $time,
            'date' => $date,
        ];
    }

    /**
     * Write the central directory and ZIP64 end records after all entries.
     */
    public function finish(): void
    {
        $centralDirectoryOffset = $this->offset;

        foreach ($this->entries as $entry) {
            $extra = pack('vv', 0x0001, 24)
                .$this->packUint64($entry['size'])
                .$this->packUint64($entry['size'])
                .$this->packUint64($entry['offset']);

            $this->write(pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014B50,
                self::ZIP64_VERSION,
                self::ZIP64_VERSION,
                self::UTF8_FLAG | self::DATA_DESCRIPTOR_FLAG,
                0,
                $entry['time'],
                $entry['date'],
                $entry['crc'],
                0xFFFFFFFF,
                0xFFFFFFFF,
                strlen($entry['name']),
                strlen($extra),
                0,
                0,
                0,
                0xFFFFFFFF,
                0xFFFFFFFF,
            ));
            $this->write($entry['name'].$extra);
        }

        $centralDirectorySize = $this->offset - $centralDirectoryOffset;
        $endOfCentralDirectoryOffset = $this->offset;

        $this->write(pack('V', 0x06064B50)
            .$this->packUint64(44)
            .pack('vvVV', self::ZIP64_VERSION, self::ZIP64_VERSION, 0, 0)
            .$this->packUint64(count($this->entries))
            .$this->packUint64(count($this->entries))
            .$this->packUint64($centralDirectorySize)
            .$this->packUint64($centralDirectoryOffset));
        $this->write(pack('VV', 0x07064B50, 0)
            .$this->packUint64($endOfCentralDirectoryOffset)
            .pack('V', 1));
        $this->write(pack('VvvvvVVv', 0x06054B50, 0, 0, 0xFFFF, 0xFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0));
    }

    /**
     * Send bytes to the sink and keep the ZIP offset in sync.
     */
    private function write(string $contents): void
    {
        $this->sink->write($contents);
        $this->offset += strlen($contents);
    }

    /**
     * Normalize a user-provided filename for use as a ZIP entry name.
     */
    private function normalizeName(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        $name = ltrim($name, '/');

        return $name === '' ? 'file' : $name;
    }

    /**
     * Convert the current timestamp to the DOS fields required by ZIP headers.
     *
     * @return array{int, int}
     */
    private function dosDateTime(): array
    {
        $now = now();

        return [
            ($now->hour << 11) | ($now->minute << 5) | intdiv($now->second, 2),
            (($now->year - 1980) << 9) | ($now->month << 5) | $now->day,
        ];
    }

    /**
     * Encode a PHP integer as the little-endian 64-bit ZIP representation.
     */
    private function packUint64(int $value): string
    {
        return pack('V2', $value & 0xFFFFFFFF, ($value >> 32) & 0xFFFFFFFF);
    }
}
