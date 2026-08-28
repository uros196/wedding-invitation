<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Upload;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Validates the client-declared metadata before allocating upload resources.
 */
final readonly class MetadataValidator
{
    /**
     * Validate the filename extension, declared size, and MIME mapping.
     */
    public function validate(string $originalName, int $size, string $mimeType): void
    {
        $extension = $this->extension($originalName);

        if ($size < 1 || $size > (int) config('memory-wall.max_file_size')) {
            throw ValidationException::withMessages([
                'size' => __('wedding.memory_wall.validation.file_size'),
            ]);
        }

        if (! $this->isSupportedExtension($extension) || ! $this->isSupportedMimeType($mimeType, $extension)) {
            throw ValidationException::withMessages([
                'file_name' => __('wedding.memory_wall.validation.file_type'),
            ]);
        }
    }

    /**
     * Retrieves the file extension from the given filename in lowercase format.
     */
    protected function extension(string $filename): string
    {
        return Str::lower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Determines if the provided file extension is supported.
     */
    protected function isSupportedExtension(string $extension): bool
    {
        return in_array($extension, config('memory-wall.allowed_extensions', []), true);
    }

    /**
     * Checks if the given MIME type is supported for the specified file extension.
     */
    protected function isSupportedMimeType(string $mimeType, string $extension): bool
    {
        return in_array($mimeType, config('memory-wall.extension_mime_types.'.$extension, []), true);
    }
}
