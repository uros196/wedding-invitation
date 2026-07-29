<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Pages;

use App\Filament\Wedding\Resources\Groups\Actions\ShareGroupAction;
use App\Filament\Wedding\Resources\Groups\Actions\ToggleInvitationSentAction;
use App\Filament\Wedding\Resources\Groups\GroupResource;
use App\Filament\Wedding\Widgets\GuestStatusWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGroup extends ViewRecord
{
    protected static string $resource = GroupResource::class;

    /**
     * Retrieves the header actions for the view group page.
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ToggleInvitationSentAction::make(),
            ShareGroupAction::make(),
        ];
    }

    /**
     * Retrieves the header widgets for the view group page.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            GuestStatusWidget::make(['group' => $this->record]),
        ];
    }
}
