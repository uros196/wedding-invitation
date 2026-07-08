<?php

namespace Tests\Feature\Filament;

use App\Enums\Age;
use App\Enums\Gender;
use App\Filament\Resources\Guests\Pages\CreateGuest;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guest can be created with age and gender', function () {
    $this->actingAs(User::factory()->create());
    $group = Group::factory()->create();

    Livewire::test(CreateGuest::class)
        ->fillForm([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'group_id' => $group->id,
            'age' => Age::Child,
            'gender' => Gender::Male,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('guests', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'age' => Age::Child->value,
        'gender' => Gender::Male->value,
    ]);
});

test('guest can be created without age and gender', function () {
    $this->actingAs(User::factory()->create());
    $group = Group::factory()->create();

    Livewire::test(CreateGuest::class)
        ->fillForm([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'group_id' => $group->id,
            'age' => null,
            'gender' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('guests', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'age' => null,
        'gender' => null,
    ]);
});
