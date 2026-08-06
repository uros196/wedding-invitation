<?php

declare(strict_types=1);

use App\Filament\Wedding\Widgets\GroupViewsWidget;
use App\Models\Group;
use Livewire\Livewire;

test('lists the five most viewed groups from the authenticated wedding in descending order', function (): void {
    $wedding = $this->user->team->wedding;
    $groups = collect(range(0, 5))->map(fn (int $views): Group => Group::factory()
        ->for($wedding)
        ->withViews($views)
        ->create(['name' => "Visible Group {$views}"]));
    $foreignGroup = Group::factory()->withViews(100)->create(['name' => 'Foreign Group']);

    $component = Livewire::test(GroupViewsWidget::class);

    expect($component->instance()->getHeading())
        ->toBe(__('wedding.widgets.group_views.heading'));

    $component
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('views_count')
        ->assertCanSeeTableRecords($groups->sortByDesc('views_count')->take(5)->all(), true)
        ->assertCanNotSeeTableRecords([$groups->first(), $foreignGroup]);
});

test('renders an empty table when the authenticated team has no wedding', function (): void {
    $team = $this->user->team;
    $team->wedding()->delete();
    $team->unsetRelation('wedding');
    $this->user->unsetRelation('team');

    $component = Livewire::test(GroupViewsWidget::class);

    expect($component->instance()->getHeading())
        ->toBe(__('wedding.widgets.group_views.heading'));

    $component->assertCountTableRecords(0);
});
