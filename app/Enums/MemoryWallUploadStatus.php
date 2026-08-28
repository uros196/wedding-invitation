<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

/**
 * States persisted for a memory wall upload session.
 */
enum MemoryWallUploadStatus: string implements HasColor, HasLabel
{
    /** The object-storage multipart session is still receiving parts. */
    case Uploading = 'uploading';

    /** All validations passed and the media is visible on the memory wall. */
    case Completed = 'completed';

    /** The session stopped because validation or object-storage processing failed. */
    case Failed = 'failed';

    /**
     * Get the localized label displayed in the Filament upload overview.
     */
    public function getLabel(): string|Htmlable|null
    {
        return __("wedding.memory_wall.status.{$this->value}");
    }

    /**
     * Retrieve the color representation associated with the current status.
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Uploading => 'warning',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }

    /**
     * Determine if the current instance represents an uploading state.
     */
    public function isUploading(): bool
    {
        return $this === self::Uploading;
    }

    /**
     * Determine if the current status represents a completed state.
     */
    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    /**
     * Determine if the current instance represents a failed state.
     */
    public function isFailed(): bool
    {
        return $this === self::Failed;
    }
}
