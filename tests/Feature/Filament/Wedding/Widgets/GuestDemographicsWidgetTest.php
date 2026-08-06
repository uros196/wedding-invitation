<?php

declare(strict_types=1);

use App\Enums\Age;
use App\Enums\Gender;
use App\Filament\Wedding\Widgets\GuestDemographicsWidget;
use App\Models\Group;
use App\Models\Guest;
use Livewire\Livewire;

test('displays total, age, and gender guest structures', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    Guest::factory()->for($group)->adult()->male()->create();
    Guest::factory()->for($group)->child()->female()->create();
    Guest::factory()->for($group)->baby()->male()->create();
    Guest::factory()->adult()->female()->create();

    Livewire::test(GuestDemographicsWidget::class)
        ->assertSee(__('wedding.widgets.guest_demographics.heading'))
        ->assertSee(__('Total Guests'))
        ->assertSee(__('Age Structure'))
        ->assertSee(__('Gender Structure'))
        ->assertSee('3')
        ->assertSee('1 / 1 / 1')
        ->assertSee('2 / 1');
});

test('fills missing demographic categories with zero and excludes foreign guests', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    Guest::factory()->for($group)->create([
        'age' => Age::Adult,
        'gender' => Gender::Male,
    ]);
    Guest::factory()->for($group)->create([
        'age' => null,
        'gender' => null,
    ]);
    Guest::factory()->create([
        'age' => Age::Baby,
        'gender' => Gender::Female,
    ]);

    Livewire::test(GuestDemographicsWidget::class)
        ->assertSee('2')
        ->assertSee('1 / 0 / 0')
        ->assertSee('1 / 0');
});

test('displays zero structures when the authenticated wedding has no guests', function (): void {
    Livewire::test(GuestDemographicsWidget::class)
        ->assertSee(__('Total Guests'))
        ->assertSee('0')
        ->assertSee('0 / 0 / 0')
        ->assertSee('0 / 0');
});
