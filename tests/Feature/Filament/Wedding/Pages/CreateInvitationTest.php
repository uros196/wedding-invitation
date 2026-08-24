<?php

declare(strict_types=1);

use App\Enums\Age;
use App\Filament\Wedding\Pages\CreateInvitation\CreateInvitation;
use App\Filament\Wedding\Resources\Groups\GroupResource;
use App\Filament\Wedding\Widgets\InvitationCreatorWidget;
use App\Models\Group;
use App\Models\Guest;
use Livewire\Livewire;

test('guides the user through creating an invitation', function (): void {
    Livewire::test(CreateInvitation::class)
        ->assertSee(__('wedding.groups.quick_create.intro.heading'))
        ->assertSee(__('wedding.groups.quick_create.steps.group'))
        ->assertSee(__('wedding.groups.quick_create.group.description'))
        ->assertSee(__('wedding.groups.quick_create.group.create_action'));
});

test('creates a group and adds guests one by one', function (): void {
    $component = Livewire::test(CreateInvitation::class)
        ->fillForm([
            'name' => 'Petrović Family',
        ], 'groupForm')
        ->call('createGroup')
        ->assertHasNoFormErrors([], 'groupForm')
        ->assertNotified()
        ->assertSet('step', 2);

    $group = Group::query()->where('name', 'Petrović Family')->firstOrFail();

    expect($group->wedding_id)->toBe($this->user->team->wedding->getKey());

    $component
        ->fillForm([
            'first_name' => 'Ana',
            'last_name' => 'Petrović',
            'age' => Age::Adult,
        ], 'guestForm')
        ->call('addGuest')
        ->assertHasNoFormErrors([], 'guestForm')
        ->assertNotified()
        ->assertSet('guestFormData.first_name', null);

    $component
        ->fillForm([
            'first_name' => 'Marko',
            'last_name' => 'Petrović',
        ], 'guestForm')
        ->call('addGuest')
        ->assertHasNoFormErrors([], 'guestForm');

    expect(Guest::query()->whereBelongsTo($group)->pluck('first_name')->all())
        ->toBe(['Ana', 'Marko']);
});

test('keeps the quick creation flow minimal and applies guest defaults', function (): void {
    Livewire::test(CreateInvitation::class)
        ->fillForm(['name' => 'Friends'], 'groupForm')
        ->call('createGroup')
        ->fillForm([
            'first_name' => 'Mina',
            'last_name' => 'Jovanović',
        ], 'guestForm')
        ->call('addGuest')
        ->assertHasNoFormErrors([], 'guestForm');

    $guest = Guest::query()->where('first_name', 'Mina')->firstOrFail();

    expect($guest->age)->toBe(Age::Adult)
        ->and($guest->status->value)->toBe('pending');
});

test('validates each step without persisting incomplete records', function (): void {
    Livewire::test(CreateInvitation::class)
        ->fillForm([], 'groupForm')
        ->call('createGroup')
        ->assertHasFormErrors(['name' => 'required'], 'groupForm')
        ->assertNotNotified();

    $component = Livewire::test(CreateInvitation::class)
        ->fillForm(['name' => 'Incomplete Group'], 'groupForm')
        ->call('createGroup')
        ->assertSet('step', 2);

    $component
        ->fillForm([], 'guestForm')
        ->call('addGuest')
        ->assertHasFormErrors(['first_name' => 'required'], 'guestForm');

    expect(Guest::query()->where('first_name', 'Only First Name')->exists())->toBeFalse();
});

test('ignores forged ownership fields in the quick creation flow', function (): void {
    $foreignGroup = Group::factory()->create();

    $component = Livewire::test(CreateInvitation::class)
        ->fillForm(['name' => 'Safe Group'], 'groupForm')
        ->set('groupFormData.wedding_id', $foreignGroup->wedding_id)
        ->set('groupFormData.uuid', 'forged-uuid')
        ->call('createGroup');

    $group = Group::query()->where('name', 'Safe Group')->firstOrFail();

    expect($group->wedding_id)->toBe($this->user->team->wedding->getKey())
        ->and($group->uuid)->not->toBe('forged-uuid');

    $component
        ->fillForm(['first_name' => 'Safe', 'last_name' => 'Guest'], 'guestForm')
        ->set('guestFormData.group_id', $foreignGroup->getKey())
        ->set('guestFormData.team_id', $foreignGroup->wedding->team_id)
        ->call('addGuest');

    $guest = Guest::query()->where('first_name', 'Safe')->firstOrFail();

    expect($guest->group_id)->toBe($group->getKey())
        ->and($guest->team_id)->toBe($this->user->team_id);
});

test('can start another invitation after finishing a group', function (): void {
    Livewire::test(CreateInvitation::class)
        ->fillForm(['name' => 'First Group'], 'groupForm')
        ->call('createGroup')
        ->call('createAnotherInvitation')
        ->assertSet('step', 1)
        ->assertSet('group', null)
        ->assertSee(__('wedding.groups.quick_create.group.create_action'));
});

test('shows the invitation creation shortcut with the current count', function (): void {
    Group::factory()->for($this->user->team->wedding)->count(2)->create();

    Livewire::test(InvitationCreatorWidget::class)
        ->assertSee(__('wedding.widgets.invitation_creator.heading'))
        ->assertSee(__('wedding.widgets.invitation_creator.description'))
        ->assertSee('2')
        ->assertSee(CreateInvitation::getUrl(), false);
});

test('returns to group management after finishing an invitation', function (): void {
    Livewire::test(CreateInvitation::class)
        ->fillForm(['name' => 'Finished Group'], 'groupForm')
        ->call('createGroup')
        ->call('finish')
        ->assertRedirect(GroupResource::getUrl());
});

test('does not allow the flow without a wedding', function (): void {
    $this->user->team->wedding()->delete();
    $this->user->unsetRelation('team');

    $this->get(CreateInvitation::getUrl())->assertForbidden();

    expect(InvitationCreatorWidget::canView())->toBeFalse();
});
