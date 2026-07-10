<?php

use App\Enums\Age;
use App\Enums\Gender;
use App\Enums\GuestStatus;
use App\Filament\Widgets\GuestAgeChartWidget;
use App\Filament\Widgets\GuestDemographicsWidget;
use App\Filament\Widgets\GuestGenderChartWidget;
use App\Filament\Widgets\GuestStatusWidget;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guest demographics widget displays correct stats', function () {
    $this->actingAs(User::factory()->create());

    Guest::factory()->count(3)->create(['age' => Age::Adult, 'gender' => Gender::Male]);
    Guest::factory()->count(2)->create(['age' => Age::Child, 'gender' => Gender::Female]);
    Guest::factory()->count(1)->create(['age' => Age::Baby, 'gender' => Gender::Female]);

    Livewire::test(GuestDemographicsWidget::class)
        ->assertSee(__('widgets.guest_demographics.total_guests.label'))
        ->assertSee('6')
        ->assertSee(__('widgets.guest_demographics.age_structure.label'))
        ->assertSee('3 / 2 / 1')
        ->assertSee(__('widgets.guest_demographics.gender_structure.label'))
        ->assertSee('3 / 3');
});

test('guest status widget displays correct stats', function () {
    $this->actingAs(User::factory()->create());

    Guest::factory()->count(3)->create(['status' => GuestStatus::Confirmed]);
    Guest::factory()->count(2)->create(['status' => GuestStatus::Declined]);
    Guest::factory()->count(1)->create(['status' => GuestStatus::Pending]);

    Livewire::test(GuestStatusWidget::class)
        ->assertSee(__('widgets.guest_status.confirmed.label'))
        ->assertSee('3')
        ->assertSee(__('widgets.guest_status.declined.label'))
        ->assertSee('2')
        ->assertSee(__('widgets.guest_status.pending.label'))
        ->assertSee('1');
});

test('guest age chart widget displays correct data', function () {
    $this->actingAs(User::factory()->create());

    Guest::factory()->count(3)->create(['age' => Age::Adult]);
    Guest::factory()->count(2)->create(['age' => Age::Child]);
    Guest::factory()->count(1)->create(['age' => null]);

    Livewire::test(GuestAgeChartWidget::class)
        ->assertSee(Age::Adult->getLabel())
        ->assertSee(Age::Child->getLabel())
        ->assertSee(__('widgets.guest_age_chart.unknown'))
        ->assertSee('3')
        ->assertSee('2')
        ->assertSee('1');
});

test('guest gender chart widget displays correct data', function () {
    $this->actingAs(User::factory()->create());

    Guest::factory()->count(3)->create(['gender' => Gender::Male]);
    Guest::factory()->count(1)->create(['gender' => null]);

    Livewire::test(GuestGenderChartWidget::class)
        ->assertSee(Gender::Male->getLabel())
        ->assertSee(__('widgets.guest_gender_chart.unknown'))
        ->assertSee('3')
        ->assertSee('1');
});
