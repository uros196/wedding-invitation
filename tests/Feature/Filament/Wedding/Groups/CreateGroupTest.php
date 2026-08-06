<?php

declare(strict_types=1);

use App\Filament\Wedding\Resources\Groups\Pages\CreateGroup;
use App\Filament\Wedding\Resources\Groups\Pages\EditGroup;
use App\Filament\Wedding\Resources\Groups\Pages\ViewGroup;
use App\Models\Group;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertModelExists;

test('does not load a group belonging to another wedding', function (): void {
    $hiddenGroup = Group::factory()->create();

    // Tenant isolation prevents IDOR access through the record parameter.
    expect(fn (): mixed => Livewire::test(ViewGroup::class, ['record' => $hiddenGroup->getKey()]))
        ->toThrow(ModelNotFoundException::class);

    // The edit page must enforce the same scope as the list and view pages.
    expect(fn (): mixed => Livewire::test(EditGroup::class, ['record' => $hiddenGroup->getKey()]))
        ->toThrow(ModelNotFoundException::class);
});

test('creates a group for the authenticated wedding', function (): void {
    Livewire::test(CreateGroup::class)
        ->fillForm([
            'name' => 'New Group',
            'invitation_title' => 'Welcome',
            'invitation_message' => 'We are happy to invite you.',
        ])
        ->call('create')
        // A valid payload is accepted without validation errors.
        ->assertHasNoFormErrors()
        // Filament sends a notification after successful creation.
        ->assertNotified()
        // The 'create' page redirects back to the resource list after saving.
        ->assertRedirect();

    $group = Group::query()->where('name', 'New Group')->firstOrFail();

    // The new model is persisted in the database.
    assertModelExists($group);
    // The ownership remains assigned to the authenticated wedding.
    expect($group->wedding_id)->toBe($this->user->team->wedding->getKey())
        // Default values are applied, and an invitation UUID is generated automatically.
        ->and($group->is_sent)->toBeFalse()
        ->and($group->has_plus_one)->toBeFalse()
        ->and($group->views_count)->toBe(0)
        ->and($group->uuid)->not->toBeEmpty();
});

test('does not allow creating a group without an associated wedding', function (): void {
    $team = Team::factory()->create();
    $user = User::factory()->weddingTeamMember()->create([
        'team_id' => $team->getKey(),
    ]);
    $groupCount = Group::query()->count();

    $this->actingAs($user, 'wedding');

    $this->get(CreateGroup::getUrl())->assertForbidden();

    // A user without a wedding cannot persist a group through the create page.
    expect(Group::query()->count())->toBe($groupCount);
});

test('does not allow a tampered wedding id during creation', function (): void {
    $otherWedding = Group::factory()->create()->wedding;

    Livewire::test(CreateGroup::class)
        ->fillForm([
            'wedding_id' => $otherWedding->getKey(),
            'name' => 'Tampered Group',
        ])
        ->call('create')
        // Tampered hidden state must not move ownership to another wedding.
        ->assertHasNoFormErrors();

    $group = Group::query()->where('name', 'Tampered Group')->firstOrFail();

    // The server keeps ownership assigned to the authenticated wedding.
    expect($group->wedding_id)->toBe($this->user->team->wedding->getKey())
        ->not->toBe($otherWedding->getKey());
});

test('validates group form input', function (array $overrides, array $errors): void {
    Livewire::test(CreateGroup::class)
        ->fillForm([
            'name' => 'Valid Group',
            ...$overrides,
        ])
        ->call('create')
        // A malicious or incomplete payload must not create a record.
        ->assertHasFormErrors($errors)
        // Failed validation must not display a success notification.
        ->assertNotNotified();
})->with([
    'name is required' => [
        ['name' => null],
        ['name' => 'required'],
    ],
    'name has a maximum length' => [
        ['name' => 'a'.Str::repeat('b', 100)],
        ['name' => 'max'],
    ],
    'invitation title requires a message' => [
        ['invitation_title' => 'Only title'],
        ['invitation_message' => 'required_with'],
    ],
    'invitation message requires a title' => [
        ['invitation_message' => 'Only message'],
        ['invitation_title' => 'required_with'],
    ],
    'invitation title has a maximum length' => [
        ['invitation_title' => Str::repeat('a', 51)],
        ['invitation_title' => 'max'],
    ],
    'invitation message has a maximum length' => [
        ['invitation_message' => Str::repeat('a', 501)],
        ['invitation_message' => 'max'],
    ],
]);
