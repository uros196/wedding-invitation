<?php

declare(strict_types=1);

use App\Enums\Age;
use App\Filament\Wedding\Widgets\GuestAgeChartWidget;
use App\Models\Group;
use App\Models\Guest;
use Livewire\Livewire;

test('builds an age chart with every category, zero values, and unknown guests', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    Guest::factory()->for($group)->adult()->count(2)->create();
    Guest::factory()->for($group)->child()->create();
    Guest::factory()->for($group)->create(['age' => null]);
    Guest::factory()->baby()->create();

    $component = Livewire::test(GuestAgeChartWidget::class);
    $data = invokeGuestAgeWidgetMethod($component->instance(), 'getData');

    expect($data['labels'])->toBe([
        Age::Adult->getLabel(),
        Age::Child->getLabel(),
        Age::Baby->getLabel(),
        __('Unknown'),
    ])
        ->and($data['datasets'][0]['label'])->toBe(__('Guests'))
        ->and($data['datasets'][0]['data'])->toBe([2, 1, 0, 1])
        ->and($data['datasets'][0]['backgroundColor'])->toBe([
            Age::Adult->chartColor(),
            Age::Child->chartColor(),
            Age::Baby->chartColor(),
            'rgba(156, 163, 175, 0.35)',
        ])
        ->and(invokeGuestAgeWidgetMethod($component->instance(), 'getType'))->toBe('polarArea');

    $component->assertSee(__('wedding.widgets.guest_age_chart.heading'));
});

test('does not append unknown to an empty age chart', function (): void {
    $component = Livewire::test(GuestAgeChartWidget::class);
    $data = invokeGuestAgeWidgetMethod($component->instance(), 'getData');

    expect($data['labels'])->toBe([
        Age::Adult->getLabel(),
        Age::Child->getLabel(),
        Age::Baby->getLabel(),
    ])
        ->and($data['datasets'][0]['data'])->toBe([0, 0, 0]);
});

/**
 * Invoke a protected chart method while keeping the production widget API unchanged.
 */
function invokeGuestAgeWidgetMethod(object $widget, string $method): mixed
{
    return (new ReflectionMethod($widget, $method))->invoke($widget);
}
