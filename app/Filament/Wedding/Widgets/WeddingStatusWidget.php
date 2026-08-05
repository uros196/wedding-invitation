<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeddingStatusWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected int|array|null $columns = 2;

    /**
     * Get the status widget heading.
     */
    protected function getHeading(): ?string
    {
        return __('wedding.widgets.wedding_status.heading');
    }

    /**
     * Get the current public access status for the wedding.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $wedding = $this->getWedding();

        $rsvpIsOpen = $wedding?->is_rsvp_open ?? false;
        $memoryWallIsOpen = $wedding?->is_memory_wall_form_open ?? false;

        return [
            Stat::make(__('RSVP'), $this->statusLabel($rsvpIsOpen))
                ->description($this->rsvpDescription($rsvpIsOpen))
                ->descriptionIcon($this->statusIcon($rsvpIsOpen))
                ->color($this->statusColor($rsvpIsOpen)),

            Stat::make(__('Memory Wall'), $this->statusLabel($memoryWallIsOpen))
                ->description($this->memoryWallDescription($memoryWallIsOpen))
                ->descriptionIcon($this->statusIcon($memoryWallIsOpen))
                ->color($this->statusColor($memoryWallIsOpen))
                ->visible(fn () => auth()->user()->can('use-memory-wall', Wedding::class)),

        ];
    }

    /**
     * Resolve the wedding belonging to the authenticated team.
     */
    protected function getWedding(): ?Wedding
    {
        $user = auth()->user();

        return $user instanceof User
            ? resolve(WeddingService::class)->getWeddingForUser($user)
            : null;
    }

    /**
     * Get a translated label for a status value.
     */
    protected function statusLabel(?bool $isOpen): string
    {
        return match ($isOpen) {
            true => __('Open'),
            false => __('Closed'),
            null => __('Not set'),
        };
    }

    /**
     * Get the RSVP status description.
     */
    protected function rsvpDescription(?bool $isOpen): string
    {
        return match ($isOpen) {
            true => __('wedding.widgets.wedding_status.rsvp.open'),
            false => __('wedding.widgets.wedding_status.rsvp.closed'),
            null => __('wedding.widgets.wedding_status.rsvp.not_set'),
        };
    }

    /**
     * Get the Memory Wall status description.
     */
    protected function memoryWallDescription(?bool $isOpen): string
    {
        return match ($isOpen) {
            true => __('wedding.widgets.wedding_status.memory_wall.open'),
            false => __('wedding.widgets.wedding_status.memory_wall.closed'),
            null => __('wedding.widgets.wedding_status.memory_wall.not_set'),
        };
    }

    /**
     * Get the icon associated with a status value.
     */
    protected function statusIcon(?bool $isOpen): Heroicon
    {
        return $isOpen === true ? Heroicon::CheckCircle : Heroicon::LockClosed;
    }

    /**
     * Get the color associated with a status value.
     */
    protected function statusColor(?bool $isOpen): string
    {
        return match ($isOpen) {
            true => 'success',
            false => 'danger',
            null => 'gray',
        };
    }
}
