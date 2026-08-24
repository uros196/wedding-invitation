<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

use App\Filament\Wedding\Pages\CreateInvitation\CreateInvitation;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvitationCreatorWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 0;

    /**
     * Determine whether there is a wedding for the invitation shortcut.
     */
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && resolve(WeddingService::class)->getWeddingForUser($user) instanceof Wedding;
    }

    /**
     * Get the widget heading.
     */
    protected function getHeading(): ?string
    {
        return __('wedding.widgets.invitation_creator.heading');
    }

    /**
     * Get the invitation shortcut statistics.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $user = auth()->user();
        $wedding = $user instanceof User
            ? resolve(WeddingService::class)->getWeddingForUser($user)
            : null;

        if (! $wedding instanceof Wedding) {
            return [];
        }

        return [
            Stat::make(__('Group Invitations'), $wedding->groups()->count())
                ->description(__('wedding.widgets.invitation_creator.description'))
                ->descriptionIcon(Heroicon::PlusCircle)
                ->color('primary')
                ->url(CreateInvitation::getUrl()),
        ];
    }
}
