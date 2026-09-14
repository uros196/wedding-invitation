<?php

declare(strict_types=1);

namespace App\Services\MemoryWall;

use Illuminate\Support\Str;

/**
 * Keeps original names readable while making duplicate names safe in a ZIP.
 */
final class ArchiveEntryNameAllocator
{
    /**
     * @var array<string, bool>
     */
    private array $usedNames = [];

    /**
     * Return a safe, unique ZIP entry name based on the original filename.
     */
    public function allocate(string $originalName): string
    {
        $name = Str::afterLast(Str::replace('\\', '/', $originalName), '/');
        $name = filled($name) ? $name : 'file';
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $basename = pathinfo($name, PATHINFO_FILENAME);
        $occurrence = 1;

        do {
            $candidate = $occurrence === 1
                ? $name
                : (filled($extension)
                    ? "{$basename} ({$occurrence}).{$extension}"
                    : "{$name} ({$occurrence})");
            $occurrence++;
        } while (isset($this->usedNames[Str::lower($candidate)]));

        $this->usedNames[Str::lower($candidate)] = true;

        return $candidate;
    }
}
