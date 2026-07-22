<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Pages;

use App\Filament\Wedding\Resources\Groups\Actions\ShareGroupAction;
use App\Filament\Wedding\Resources\Groups\Actions\ToggleInvitationSentAction;
use App\Filament\Wedding\Resources\Groups\GroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleInvitationSentAction::make(),
            ShareGroupAction::make(),
            DeleteAction::make(),
        ];
    }
}
