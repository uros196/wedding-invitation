<?php

use App\Filament\Pages\ManageWedding;
use App\Filament\Widgets\InvitationStats;
use App\Models\User;
use App\Models\Wedding;
use Livewire\Livewire;

test('authenticated users can visit manage wedding page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('filament.admin.pages.manage-wedding'));

    if ($response->status() === 403) {
        $response->dump();
    }

    $response->assertOk();
});

test('wedding details can be saved', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ManageWedding::class)
        ->fillForm([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2026-08-20 16:00:00',
            'rsvp_deadline' => '2026-08-01 00:00:00',
            'welcome_text' => 'Dobrodošli na naše venčanje!',
            'schedules' => [
                ['name' => 'Crkva', 'enabled' => true, 'time' => '16:00', 'location' => 'Hram Svetog Save'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $wedding = Wedding::first();
    expect($wedding->bride_name)->toBe('Ana');
});

test('invitation stats widget shows wedding info', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Sa vencanjem
    Wedding::create([
        'bride_name' => 'Ana',
        'groom_name' => 'Marko',
        'wedding_date' => now()->addDays(10),
        'rsvp_deadline' => now()->addDays(5),
    ]);

    Livewire::test(InvitationStats::class)
        ->assertSee('dana')
        ->assertSee('Poslate pozivnice');
});
