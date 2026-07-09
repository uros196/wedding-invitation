<?php

use App\Enums\GuestStatus;
use App\Livewire\GlobalExport;
use App\Models\Guest;
use App\Models\User;
use Livewire\Livewire;

it('can render global export component', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(GlobalExport::class)
        ->assertStatus(200)
        ->assertSee('Izvezi potvrđene goste');
});

it('can trigger export guests action', function () {
    $this->actingAs(User::factory()->create());

    // Create some guests
    Guest::factory()->count(3)->create(['status' => GuestStatus::Confirmed]);
    Guest::factory()->count(2)->create(['status' => GuestStatus::Pending]);

    Livewire::test(GlobalExport::class)
        ->callAction('exportGuests', data: [
            'columnMap' => [
                'id' => 'id',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
            ],
            'format' => 'xlsx',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseCount('exports', 1);
});
