<?php

declare(strict_types=1);

use App\Filament\Wedding\Widgets\InvitationStats;
use App\Models\Group;
use Livewire\Livewire;

test('displays sent invitations and total views for the authenticated wedding only', function (): void {
    $wedding = $this->user->team->wedding;
    Group::factory()->for($wedding)->sent()->withViews(5)->create();
    Group::factory()->for($wedding)->unsent()->withViews(7)->create();
    Group::factory()->sent()->withViews(100)->create();

    Livewire::test(InvitationStats::class)
        ->assertSee(__('wedding.widgets.invitation_stats.heading'))
        ->assertSee(__('Sent Invitations'))
        ->assertSee(__('Total Views'))
        ->assertSee('1')
        ->assertSee('12');
});

test('displays future wedding and RSVP countdowns', function (): void {
    $wedding = $this->user->team->wedding;
    $wedding->update([
        'wedding_date' => now()->addDays(10),
        'rsvp_deadline' => now()->addDays(5),
    ]);

    Livewire::test(InvitationStats::class)
        ->assertSee(config('wedding.widgets.countdown.label.wedding_until'))
        ->assertSee(config('wedding.widgets.countdown.label.application_until'))
        ->assertSee($wedding->wedding_date->format('d.m.Y'))
        ->assertSee($wedding->rsvp_deadline->format('d.m.Y H:i'));
});

test('displays past wedding and RSVP countdown labels', function (): void {
    $wedding = $this->user->team->wedding;
    $wedding->update([
        'wedding_date' => now()->subDays(10),
        'rsvp_deadline' => now()->subDays(5),
    ]);

    Livewire::test(InvitationStats::class)
        ->assertSee(config('wedding.widgets.countdown.label.wedding_past'))
        ->assertSee(config('wedding.widgets.countdown.label.application_past'));
});

test('omits wedding-specific stats when the authenticated team has no wedding', function (): void {
    $team = $this->user->team;
    $team->wedding()->delete();
    $team->unsetRelation('wedding');
    $this->user->unsetRelation('team');

    Livewire::test(InvitationStats::class)
        ->assertSee(__('Sent Invitations'))
        ->assertSee(__('Total Views'))
        ->assertSee('0')
        ->assertDontSee(config('wedding.widgets.countdown.label.wedding_until'))
        ->assertDontSee(config('wedding.widgets.countdown.label.application_until'))
        ->assertDontSee(config('wedding.widgets.countdown.label.wedding_past'))
        ->assertDontSee(config('wedding.widgets.countdown.label.application_past'));
});

test('omits countdown stats when their dates are not set', function (): void {
    $this->user->team->wedding->update([
        'wedding_date' => null,
        'rsvp_deadline' => null,
    ]);

    Livewire::test(InvitationStats::class)
        ->assertSee(__('Sent Invitations'))
        ->assertSee(__('Total Views'))
        ->assertDontSee(config('wedding.widgets.countdown.label.wedding_until'))
        ->assertDontSee(config('wedding.widgets.countdown.label.application_until'))
        ->assertDontSee(config('wedding.widgets.countdown.label.wedding_past'))
        ->assertDontSee(config('wedding.widgets.countdown.label.application_past'));
});
