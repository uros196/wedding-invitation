<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Guests\Pages\CreateGuest;
use App\Filament\Resources\Guests\Pages\EditGuest;
use App\Models\Guest;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('create guest page can be rendered', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateGuest::class)
        ->assertSuccessful();
});

test('edit guest page can be rendered', function () {
    $this->actingAs(User::factory()->create());
    $guest = Guest::factory()->create();

    Livewire::test(EditGuest::class, ['record' => $guest->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('Kompanjoni');
});

test('add companion action is visible', function () {
    $this->actingAs(User::factory()->create());
    $guest = Guest::factory()->create();

    // Dodajemo kompanjona da bi se videlo dugme u headeru
    Guest::factory()->create(['parent_id' => $guest->id]);

    Livewire::test(EditGuest::class, ['record' => $guest->getRouteKey()])
        ->assertActionVisible(TestAction::make('addCompanion')->schemaComponent('companions_section'));
});

test('companion can be removed from guest', function () {
    $this->actingAs(User::factory()->create());

    $parent = Guest::factory()->create();
    $companion = Guest::factory()->create([
        'parent_id' => $parent->id,
        'first_name' => 'Companion',
        'last_name' => 'Name',
    ]);

    Livewire::test(EditGuest::class, ['record' => $parent->getRouteKey()])
        ->assertSee('Companion Name')
        ->callAction(TestAction::make("removeCompanion_{$companion->id}")->schemaComponent('companions_section'))
        ->assertHasNoActionErrors()
        ->assertDontSee('Companion Name');

    expect($companion->refresh()->parent_id)->toBeNull();
});

test('companion can be added to guest', function () {
    $this->actingAs(User::factory()->create());

    $parent = Guest::factory()->create();
    $potentialCompanion = Guest::factory()->create(['first_name' => 'New', 'last_name' => 'Companion']);

    Livewire::test(EditGuest::class, ['record' => $parent->getRouteKey()])
        ->assertDontSee('New Companion')
        ->callAction(TestAction::make('addCompanion')->schemaComponent('companions_section'), [
            'guest_id' => $potentialCompanion->id,
        ])
        ->assertHasNoActionErrors()
        ->assertSee('New Companion');

    expect($potentialCompanion->refresh()->parent_id)->toBe($parent->id);
});
