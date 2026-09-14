<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Describes the lifecycle of an asynchronously prepared archive.
 */
enum MemoryWallDownloadStatus: string implements HasColor, HasLabel
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
    case Expired = 'expired';
    case Cancelling = 'cancelling';
    case Cancelled = 'cancelled';

    /**
     * Get the translated status label displayed in the download manager.
     */
    public function getLabel(): string
    {
        return __("wedding.memory_wall.download.status.{$this->value}");
    }

    /**
     * Get the Filament color associated with the current lifecycle state.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Queued => 'gray',
            self::Processing => 'info',
            self::Ready => 'success',
            self::Failed => 'danger',
            self::Expired => 'warning',
            self::Cancelling => 'warning',
            self::Cancelled => 'gray',
        };
    }

    /**
     * Get the presentation details used by the memory wall download manager.
     *
     * @return array{icon: string, iconClass: string, badgeClass: string}
     */
    public function getDownloadManagerStyle(): array
    {
        return match ($this) {
            self::Ready => [
                'icon' => 'heroicon-o-check-circle',
                'iconClass' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400',
                'badgeClass' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
            ],
            self::Failed => [
                'icon' => 'heroicon-o-exclamation-triangle',
                'iconClass' => 'bg-rose-50 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400',
                'badgeClass' => 'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300',
            ],
            self::Queued, self::Processing, self::Expired, self::Cancelling => [
                'icon' => 'heroicon-o-arrow-path',
                'iconClass' => 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400',
                'badgeClass' => 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
            ],
            self::Cancelled => [
                'icon' => 'heroicon-o-x-circle',
                'iconClass' => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400',
                'badgeClass' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400',
            ],
        };
    }

    /**
     * Determine whether the archive is still being prepared.
     */
    public function isActive(): bool
    {
        return in_array($this, [self::Queued, self::Processing], true);
    }

    /**
     * Determine whether the archive is ready for the browser to download.
     */
    public function isReady(): bool
    {
        return $this->is(self::Ready);
    }

    /**
     * Determine whether a worker is still preparing archive bytes.
     */
    public function isPreparing(): bool
    {
        return in_array($this, [self::Queued, self::Processing], true);
    }

    /**
     * Determine whether a cancellation request is waiting for worker cleanup.
     */
    public function isCancelling(): bool
    {
        return $this->is(self::Cancelling);
    }

    /**
     * Determine whether the archive can be removed without interrupting a worker.
     */
    public function canBeRemoved(): bool
    {
        return in_array($this, [self::Ready, self::Failed, self::Expired, self::Cancelled], true);
    }

    /**
     * Check if the current status is equal to the given status.
     */
    public function is(self $status): bool
    {
        return $this === $status;
    }
}
