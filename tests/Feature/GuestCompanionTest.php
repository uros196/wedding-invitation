<?php

use App\Models\Group;
use App\Models\Guest;
use App\Services\GuestService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a guest can have companions', function () {
    $group = Group::factory()->create();
    $guest = Guest::factory()->create(['group_id' => $group->id]);

    $companion = Guest::factory()->create([
        'group_id' => $group->id,
        'parent_id' => $guest->id,
    ]);

    expect($guest->companions)->toHaveCount(1)
        ->and($guest->companions->first()->id)->toBe($companion->id)
        ->and($companion->parent->id)->toBe($guest->id);
});

test('syncCompanions correctly links and updates group for companions', function () {
    $group = Group::factory()->create();
    $parent = Guest::factory()->create(['group_id' => $group->id]);
    $guest1 = Guest::factory()->create(['group_id' => Group::factory()->create()->id]);
    $guest2 = Guest::factory()->create(['group_id' => Group::factory()->create()->id]);

    $service = new GuestService;
    $service->syncCompanions($parent, [$guest1->id, $guest2->id]);

    $guest1->refresh();
    $guest2->refresh();

    expect($guest1->parent_id)->toBe($parent->id)
        ->and($guest1->group_id)->toBe($parent->group_id)
        ->and($guest2->parent_id)->toBe($parent->id)
        ->and($guest2->group_id)->toBe($parent->group_id);
});

test('companions are automatically assigned to the same group as parent when parent group changes', function () {
    $oldGroup = Group::factory()->create(['name' => 'Old Group']);
    $newGroup = Group::factory()->create(['name' => 'New Group']);

    $guest = Guest::factory()->create(['group_id' => $oldGroup->id]);
    $companion = Guest::factory()->create([
        'group_id' => $oldGroup->id,
        'parent_id' => $guest->id,
    ]);

    expect($companion->group_id)->toBe($oldGroup->id);

    // Change parent's group
    $guest->update(['group_id' => $newGroup->id]);

    // Companion should also be in the new group
    $companion->refresh();
    expect($companion->group_id)->toBe($newGroup->id);
});

test('changing companion group does not change parent group', function () {
    $groupA = Group::factory()->create(['name' => 'Group A']);
    $groupB = Group::factory()->create(['name' => 'Group B']);

    $guest = Guest::factory()->create(['group_id' => $groupA->id]);
    $companion = Guest::factory()->create([
        'group_id' => $groupA->id,
        'parent_id' => $guest->id,
    ]);

    // Change companion's group
    $companion->update(['group_id' => $groupB->id]);

    $guest->refresh();
    expect($guest->group_id)->toBe($groupA->id)
        ->and($companion->group_id)->toBe($groupB->id);
});
