<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Messages\Pages;

use App\Filament\Wedding\Resources\Groups\GroupResource;
use App\Filament\Wedding\Resources\Messages\MessageResource;
use App\Models\Message;
use App\Services\MessageService;
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

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $user = auth()->user();

        /** @var Message $message */
        $message = $this->getRecord();

        resolve(MessageService::class)->markAsRead($user, $message);
    }
}
