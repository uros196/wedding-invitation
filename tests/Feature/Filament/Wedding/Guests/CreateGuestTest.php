<?php

declare(strict_types=1);

use App\Enums\Age;
use App\Enums\GuestStatus;
use App\Filament\Wedding\Resources\Guests\Pages\CreateGuest;
use App\Models\Group;
use App\Models\Guest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertModelExists;

test('creates a guest for the authenticated wedding with default values', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();

    Livewire::test(CreateGuest::class)
        ->fillForm([
            'first_name' => 'Ana',
            'last_name' => 'Guest',
            'group_id' => $group->getKey(),
        ])
        ->call('create')
        // A valid payload is accepted without validation errors.
        ->assertHasNoFormErrors()
        // Filament notifies the user and returns to the resource list.
        ->assertNotified()
        ->assertRedirect();

    $guest = Guest::query()->where('first_name', 'Ana')->firstOrFail();

    assertModelExists($guest);
    // Defaults and ownership are assigned by the resource and observer.
    expect($guest->status)->toBe(GuestStatus::Pending)
        ->and($guest->age)->toBe(Age::Adult)
        ->and($guest->group_id)->toBe($group->getKey())
        ->and($guest->team_id)->toBe($this->user->team_id);
});

test('does not allow creating a guest without an associated wedding', function (): void {
    $team = Team::factory()->create();
    $user = User::factory()->weddingTeamMember()->create([
        'team_id' => $team->getKey(),
    ]);
    $guestCount = Guest::withoutGlobalScopes()->count();

    $this->actingAs($user, 'wedding');

    $this->get(CreateGuest::getUrl())->assertForbidden();

    // A user without a wedding cannot persist a guest through the create page.
    expect(Guest::withoutGlobalScopes()->count())->toBe($guestCount);
});

test('does not allow a guest to use a group from another wedding', function (): void {
    $foreignGroup = Group::factory()->create();

    Livewire::test(CreateGuest::class)
        ->fillForm([
            'first_name' => 'Tampered Guest',
            'group_id' => $foreignGroup->getKey(),
        ])
        ->call('create')
        // A group outside the current wedding is rejected by the relationship field.
        ->assertHasFormErrors(['group_id'])
        ->assertNotNotified();

    expect(Guest::withoutGlobalScopes()
        ->where('first_name', 'Tampered Guest')
        ->exists())->toBeFalse();
});

test('ignores forged ownership fields during creation', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $foreignGuest = Guest::factory()->create();

    Livewire::test(CreateGuest::class)
        ->fillForm([
            'first_name' => 'Protected Guest',
            'group_id' => $group->getKey(),
            'team_id' => $foreignGuest->team_id,
            'parent_id' => $foreignGuest->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $guest = Guest::query()->where('first_name', 'Protected Guest')->firstOrFail();

    // Hidden form state cannot move ownership or assign a companion.
    expect($guest->team_id)->toBe($this->user->team_id)
        ->and($guest->parent_id)->toBeNull();
});

test('validates guest form input', function (array $overrides, array $errors): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();

    Livewire::test(CreateGuest::class)
        ->fillForm([
            'first_name' => 'Valid Guest',
            'group_id' => $group->getKey(),
            ...$overrides,
        ])
        ->call('create')
        // Invalid or incomplete input must not be persisted.
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    'first name is required' => [
        ['first_name' => null],
        ['first_name' => 'required'],
    ],
    'first name has a maximum length' => [
        ['first_name' => Str::repeat('a', 51)],
        ['first_name' => 'max'],
    ],
    'last name has a maximum length' => [
        ['last_name' => Str::repeat('a', 51)],
        ['last_name' => 'max'],
    ],
    'group is required' => [
        ['group_id' => null],
        ['group_id' => 'required'],
    ],
]);
