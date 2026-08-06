<?php

declare(strict_types=1);

use App\Filament\Wedding\Widgets\GuestStatusWidget;
use App\Models\Group;
use App\Models\Guest;
use Livewire\Livewire;

test('displays guest response counts for the authenticated wedding', function (): void {
    $wedding = $this->user->team->wedding;
    $group = Group::factory()->for($wedding)->create();
    Guest::factory()->for($group)->confirmed()->count(2)->create();
    Guest::factory()->for($group)->declined()->create();
    Guest::factory()->for($group)->pending()->count(3)->create();
    Guest::factory()->confirmed()->create();

    Livewire::test(GuestStatusWidget::class)
        ->assertSee(__('wedding.widgets.guest_status.heading'))
        ->assertSee(__('Confirmed'))
        ->assertSee(__('Declined'))
        ->assertSee(__('Pending'))
        ->assertSee(__('wedding.widgets.guest_status.confirmed.description'))
        ->assertSee(__('wedding.widgets.guest_status.declined.description'))
        ->assertSee(__('wedding.widgets.guest_status.pending.description'))
        ->assertSee('2')
        ->assertSee('1')
        ->assertSee('3');
});

test('limits guest response counts to the supplied group', function (): void {
    $wedding = $this->user->team->wedding;
    $group = Group::factory()->for($wedding)->create();
    $otherGroup = Group::factory()->for($wedding)->create();
    Guest::factory()->for($group)->confirmed()->create();
    Guest::factory()->for($otherGroup)->declined()->count(2)->create();

    Livewire::test(GuestStatusWidget::class, ['group' => $group])
        ->assertSee(__('Confirmed'))
        ->assertSee(__('Declined'))
        ->assertSee(__('Pending'))
        ->assertSee('1')
        ->assertSee('0');
});

test('does not expose counts from a group belonging to another wedding', function (): void {
    $foreignGroup = Group::factory()->create();
    Guest::factory()->for($foreignGroup)->confirmed()->count(3)->create();

    Livewire::test(GuestStatusWidget::class, ['group' => $foreignGroup])
        ->assertSee(__('Confirmed'))
        ->assertSee('0');
});

test('refreshes when the guest status refresh event is received', function (): void {
    Livewire::test(GuestStatusWidget::class)
        ->dispatch('refresh-guest-status-widget')
        ->assertDispatched('$refresh');
});
