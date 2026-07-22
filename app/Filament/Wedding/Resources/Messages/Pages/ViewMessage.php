<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Messages\Pages;

use App\Filament\Wedding\Resources\Groups\GroupResource;
use App\Filament\Wedding\Resources\Messages\MessageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewGroup')
                ->label(__('View Group'))
                ->url(fn (): string => GroupResource::getUrl('edit', ['record' => $this->getRecord()->group_id])),
        ];
    }
}
