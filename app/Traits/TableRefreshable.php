<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Arr;

trait TableRefreshable
{
    /**
     * Define listeners for the Livewire component.
     */
    public function getListeners(): array
    {
        if (blank($this->refreshTableOn())) {
            return $this->listeners;
        }

        $team = auth()->user()->team;

        return collect(Arr::wrap($this->refreshTableOn()))
            ->mapWithKeys(function (string $event) use ($team) {
                return [
                    "echo-private:{$team->broadcastChannelName()},{$event}" => 'refreshTable',
                ];
            })
            ->all();
    }

    /**
     * Refresh the table when a new message is broadcast for the current wedding.
     */
    public function refreshTable(): void
    {
        $this->dispatch('$refresh');
    }

    /**
     * Retrieve a list of broadcast events that the table should refresh when triggered.
     */
    protected function refreshTableOn(): string|array
    {
        return [];
    }
}
