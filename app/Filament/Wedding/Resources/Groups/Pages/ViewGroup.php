<?php

namespace App\Filament\Wedding\Resources\Groups\Pages;

use App\Filament\Wedding\Resources\Groups\Actions\ShareGroupAction;
use App\Filament\Wedding\Resources\Groups\Actions\ToggleInvitationSentAction;
use App\Filament\Wedding\Resources\Groups\GroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGroup extends ViewRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ToggleInvitationSentAction::make(),
            ShareGroupAction::make(),
        ];
    }
}
