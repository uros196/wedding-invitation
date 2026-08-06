<?php

declare(strict_types=1);

use App\Enums\Gender;
use App\Filament\Wedding\Widgets\GuestGenderChartWidget;
use App\Models\Group;
use App\Models\Guest;
use Livewire\Livewire;

test('builds a gender chart with every category, zero values, and unknown guests', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    Guest::factory()->for($group)->male()->count(2)->create();
    Guest::factory()->for($group)->create(['gender' => null]);
    Guest::factory()->female()->create();

    $component = Livewire::test(GuestGenderChartWidget::class);
    $data = invokeGuestGenderWidgetMethod($component->instance(), 'getData');

    expect($data['labels'])->toBe([
        Gender::Male->getLabel(),
        Gender::Female->getLabel(),
        __('Unknown'),
    ])
        ->and($data['datasets'][0]['label'])->toBe(__('Guests'))
        ->and($data['datasets'][0]['data'])->toBe([2, 0, 1])
        ->and($data['datasets'][0]['backgroundColor'])->toBe([
            Gender::Male->chartColor(),
            Gender::Female->chartColor(),
            'rgba(156, 163, 175, 0.6)',
        ])
        ->and(invokeGuestGenderWidgetMethod($component->instance(), 'getType'))->toBe('doughnut');

    $component->assertSee(__('wedding.widgets.guest_gender_chart.heading'));
});

test('does not append unknown to an empty gender chart', function (): void {
    $component = Livewire::test(GuestGenderChartWidget::class);
    $data = invokeGuestGenderWidgetMethod($component->instance(), 'getData');

    expect($data['labels'])->toBe([
        Gender::Male->getLabel(),
        Gender::Female->getLabel(),
    ])
        ->and($data['datasets'][0]['data'])->toBe([0, 0]);
});

/**
 * Invoke a protected chart method while keeping the production widget API unchanged.
 */
function invokeGuestGenderWidgetMethod(object $widget, string $method): mixed
{
    return (new ReflectionMethod($widget, $method))->invoke($widget);
}
