<?php

declare(strict_types=1);

use App\Enums\GuestStatus;
use App\Filament\Wedding\Resources\Guests\Pages\ViewGuest;
use App\Models\Group;
use App\Models\Guest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('shows guest details and companions', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create([
        'name' => 'Details Group',
    ]);
    $guest = Guest::factory()->for($group)->confirmed()->create([
        'first_name' => 'Ana',
        'last_name' => 'Guest',
        'notes' => 'Dietary note',
    ]);
    $companion = Guest::factory()->for($group)->companionOf($guest)->create([
        'first_name' => 'Boris',
        'last_name' => 'Companion',
    ]);

    Livewire::test(ViewGuest::class, ['record' => $guest->getKey()])
        // The infolist displays the guest's direct and related information.
        ->assertSee($guest->full_name)
        ->assertSee($group->name)
        ->assertSee(GuestStatus::Confirmed->getLabel())
        ->assertSee($guest->notes)
        ->assertSee($companion->full_name);
});

test('does not load a guest belonging to another wedding', function (): void {
    $hiddenGuest = Guest::factory()->create();

    // Tenant isolation prevents IDOR access through the record parameter.
    expect(fn (): mixed => Livewire::test(ViewGuest::class, [
        'record' => $hiddenGuest->getKey(),
    ]))->toThrow(ModelNotFoundException::class);
});
