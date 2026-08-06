<?php

declare(strict_types=1);

use App\Filament\Wedding\Widgets\WeddingStatusWidget;
use Livewire\Livewire;

test('displays open RSVP and memory wall statuses', function (): void {
    $wedding = $this->user->team->wedding;
    $wedding->update([
        'rsvp_deadline' => now()->addDay(),
        'wedding_date' => now()->subDay(),
        'memory_wall_open_until' => now()->addDay(),
    ]);

    Livewire::test(WeddingStatusWidget::class)
        ->assertSee(__('wedding.widgets.wedding_status.heading'))
        ->assertSee(__('RSVP'))
        ->assertSee(__('Open'))
        ->assertSee(__('wedding.widgets.wedding_status.rsvp.open'))
        ->assertSee(__('Memory Wall'))
        ->assertSee(__('wedding.widgets.wedding_status.memory_wall.open'))
        ->assertSee('fi-color-success');
});

test('displays closed RSVP and memory wall statuses before the wedding', function (): void {
    $wedding = $this->user->team->wedding;
    $wedding->update([
        'rsvp_deadline' => now()->subDay(),
        'wedding_date' => now()->addDay(),
    ]);

    Livewire::test(WeddingStatusWidget::class)
        ->assertSee(__('RSVP'))
        ->assertSee(__('Closed'))
        ->assertSee(__('wedding.widgets.wedding_status.rsvp.closed'))
        ->assertSee(__('Memory Wall'))
        ->assertSee(__('wedding.widgets.wedding_status.memory_wall.closed'))
        ->assertSee('fi-color-danger');
});

test('hides the memory wall when the team has disabled it', function (): void {
    $this->user->team->update(['has_memory_wall' => false]);

    Livewire::test(WeddingStatusWidget::class)
        ->assertSee(__('RSVP'))
        ->assertDontSee(__('Memory Wall'));
});

test('handles unset wedding dates as closed and not set statuses', function (): void {
    $this->user->team->wedding->update([
        'wedding_date' => null,
        'rsvp_deadline' => null,
        'memory_wall_open_until' => null,
    ]);

    Livewire::test(WeddingStatusWidget::class)
        ->assertSee(__('Not set'))
        ->assertSee(__('wedding.widgets.wedding_status.rsvp.not_set'))
        ->assertSee(__('Closed'))
        ->assertSee(__('wedding.widgets.wedding_status.memory_wall.closed'));
});

test('displays not set statuses when the authenticated team has no wedding', function (): void {
    $team = $this->user->team;
    $team->wedding()->delete();
    $team->unsetRelation('wedding');
    $this->user->unsetRelation('team');

    Livewire::test(WeddingStatusWidget::class)
        ->assertSee(__('Not set'))
        ->assertSee(__('wedding.widgets.wedding_status.rsvp.not_set'))
        ->assertSee(__('wedding.widgets.wedding_status.memory_wall.not_set'));
});
