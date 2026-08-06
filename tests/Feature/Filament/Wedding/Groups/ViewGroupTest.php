<?php

declare(strict_types=1);

use App\Filament\Wedding\Resources\Groups\Pages\ViewGroup;
use App\Filament\Wedding\Resources\Groups\RelationManagers\GuestsRelationManager;
use App\Models\Group;
use App\Models\Guest;
use Livewire\Livewire;

test('shows group details and its guests', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->withMeta()->create([
        'name' => 'Details Group',
    ]);
    $guest = Guest::factory()->for($group)->create([
        'first_name' => 'Ana',
        'last_name' => 'Test',
    ]);

    Livewire::test(ViewGroup::class, ['record' => $group->getKey()])
        // The view page displays basic and computed values.
        ->assertSchemaStateSet([
            'name' => 'Details Group',
            'uuid' => $group->uuid,
            'guests_count' => 1,
            'messages_count' => 0,
            'views_count' => 0,
        ])
        // The relation manager is rendered on the view page.
        ->assertSeeLivewire(GuestsRelationManager::class);

    Livewire::test(GuestsRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => ViewGroup::class,
    ])
        // The relation manager loads the guest through the owner group's relationship.
        ->assertCanSeeTableRecords([$guest]);
});
