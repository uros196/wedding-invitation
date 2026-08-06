<?php

declare(strict_types=1);

use App\Enums\Age;
use App\Enums\Gender;
use App\Enums\GuestStatus;
use App\Filament\Wedding\Resources\Guests\Pages\EditGuest;
use App\Models\Group;
use App\Models\Guest;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

use function Pest\Laravel\assertModelMissing;

test('edits guest details without changing ownership', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $foreignGuest = Guest::factory()->create();
    $guest = Guest::factory()->for($group)->create([
        'first_name' => 'Before',
        'last_name' => 'Edit',
    ]);

    Livewire::test(EditGuest::class, ['record' => $guest->getKey()])
        ->fillForm([
            'first_name' => 'After',
            'last_name' => 'Updated',
            'group_id' => $group->getKey(),
            'status' => GuestStatus::Confirmed->value,
            'age' => Age::Child->value,
            'gender' => Gender::Female->value,
            'notes' => 'Updated note',
            'team_id' => $foreignGuest->team_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        // Edit pages notify after saving and remain on the page.
        ->assertNotified();

    $guest->refresh();

    expect($guest->first_name)->toBe('After')
        ->and($guest->last_name)->toBe('Updated')
        ->and($guest->status)->toBe(GuestStatus::Confirmed)
        ->and($guest->age)->toBe(Age::Child)
        ->and($guest->gender)->toBe(Gender::Female)
        ->and($guest->notes)->toBe('Updated note')
        // Forged ownership state is not part of the editable form.
        ->and($guest->team_id)->toBe($this->user->team_id);
});

test('synchronizes companion groups when the parent group changes', function (): void {
    $oldGroup = Group::factory()->for($this->user->team->wedding)->create();
    $newGroup = Group::factory()->for($this->user->team->wedding)->create();
    $parent = Guest::factory()->for($oldGroup)->create();
    $companion = Guest::factory()->for($oldGroup)->companionOf($parent)->create();

    Livewire::test(EditGuest::class, ['record' => $parent->getKey()])
        ->fillForm([
            'first_name' => $parent->first_name,
            'group_id' => $newGroup->getKey(),
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    // Changing a parent group keeps all of its companions in the same group.
    expect($parent->refresh()->group_id)->toBe($newGroup->getKey())
        ->and($companion->refresh()->group_id)->toBe($newGroup->getKey());
});

test('deletes a guest from the edit page', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $guest = Guest::factory()->for($group)->create();

    Livewire::test(EditGuest::class, ['record' => $guest->getKey()])
        ->callAction(DeleteAction::class)
        // Filament displays a success notification after deletion.
        ->assertNotified();

    assertModelMissing($guest);
});
