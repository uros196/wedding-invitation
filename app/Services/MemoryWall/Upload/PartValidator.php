<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Upload;

use App\Models\MemoryWallUpload;
use Illuminate\Validation\ValidationException;

/**
 * Verifies that object storage received the complete expected part layout.
 */
final readonly class PartValidator
{
    /**
     * Sort and validate all parts before they are sent to the completion call.
     *
     * @param  array<int, array{part_number: int, etag: string, size: int}>  $parts
     * @return array<int, array{part_number: int, etag: string, size: int}>
     */
    public function validate(MemoryWallUpload $upload, array $parts): array
    {
        usort($parts, static fn (array $left, array $right): int => $left['part_number'] <=> $right['part_number']);

        if (! $this->isPartsNumberExpected($upload, $parts) || ! $this->isSizeExpected($upload, $parts)) {
            throw ValidationException::withMessages([
                'parts' => __('wedding.memory_wall.validation.parts_incomplete'),
            ]);
        }

        foreach ($parts as $part) {
            if (blank($part['etag']) || $part['size'] < 1) {
                throw ValidationException::withMessages([
                    'parts' => __('wedding.memory_wall.validation.parts_incomplete'),
                ]);
            }
        }

        return $parts;
    }

    /**
     * Verifies if the part numbers of the provided parts match the expected sequence of part numbers for the upload.
     */
    protected function isPartsNumberExpected(MemoryWallUpload $upload, array $parts): bool
    {
        $expectedPartNumbers = range(1, $upload->total_parts);
        $actualPartNumbers = array_column($parts, 'part_number');

        return $actualPartNumbers === $expectedPartNumbers;
    }

    /**
     * Determines if the total size of the provided parts matches the expected size of the upload.
     */
    protected function isSizeExpected(MemoryWallUpload $upload, array $parts): bool
    {
        return array_sum(array_column($parts, 'size')) === $upload->expected_size;
    }
}
