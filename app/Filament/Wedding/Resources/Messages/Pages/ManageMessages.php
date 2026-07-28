<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Messages\Pages;

use App\Filament\Wedding\Resources\Messages\MessageResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMessages extends ManageRecords
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    /**
     * Define listeners for the Livewire component.
     */
    public function getListeners(): array
    {
        $team = auth()->user()->team;

        return [
            "echo-private:{$team->broadcastChannelName()},.messageReceived" => 'refreshMessagesTable',
        ];
    }

    /**
     * Refresh the table when a new message is broadcast for the current wedding.
     */
    public function refreshMessagesTable(): void
    {
        $this->resetTable();
    }
}
