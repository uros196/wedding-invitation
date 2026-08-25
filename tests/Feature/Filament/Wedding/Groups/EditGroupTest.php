<?php

declare(strict_types=1);

use App\Filament\Wedding\Pages\ManageWedding\ManageWedding;
use App\Filament\Wedding\Resources\Groups\Pages\EditGroup;
use App\Models\Group;
use App\Models\Guest;
use App\Models\Message;
use App\Models\WeddingTimeline;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

use function Pest\Laravel\assertModelMissing;

test('edits a group without changing protected fields', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->withViews(12)->create([
        'name' => 'Before Edit',
        'uuid' => '11111111-1111-4111-8111-111111111111',
    ]);

    Livewire::test(EditGroup::class, ['record' => $group->getKey()])
        ->fillForm([
            'wedding_id' => Group::factory()->create()->wedding_id,
            'uuid' => '22222222-2222-4222-8222-222222222222',
            'views_count' => 999,
            'name' => 'After Edit',
            'invitation_title' => 'Updated title',
            'invitation_message' => 'Updated message',
        ])
        ->call('save')
        // A valid editable payload is accepted.
        ->assertHasNoFormErrors()
        // The edit page displays a notification but does not require a redirect.
        ->assertNotified();

    $group->refresh();

    // Editable fields are updated.
    expect($group->name)->toBe('After Edit')
        ->and($group->invitation_title)->toBe('Updated title')
        ->and($group->invitation_message)->toBe('Updated message');
    // Disabled/dehydrated(false) fields cannot be forged.
    expect($group->uuid)->toBe('11111111-1111-4111-8111-111111111111')
        ->and($group->views_count)->toBe(12)
        ->and($group->wedding_id)->toBe($this->user->team->wedding->getKey());
});

test('toggles invitation status from the edit page', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->unsent()->create();

    Livewire::test(EditGroup::class, ['record' => $group->getKey()])
        ->callAction(TestAction::make('toggleInvitationSent'))
        // The action completes successfully without form errors.
        ->assertHasNoFormErrors();

    // The invitation changes from unsent to sent.
    expect($group->refresh()->is_sent)->toBeTrue();

    Livewire::test(EditGroup::class, ['record' => $group->getKey()])
        ->callAction(TestAction::make('toggleInvitationSent'))
        // The same action must be reversible.
        ->assertHasNoFormErrors();

    // The invitation changes back from sent to unsent.
    expect($group->refresh()->is_sent)->toBeFalse();
});

test('deletes a group and its related messages', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $message = Message::factory()->for($group)->create();

    Livewire::test(EditGroup::class, ['record' => $group->getKey()])
        ->callAction(DeleteAction::class)
        // Filament displays a success notification after deletion.
        ->assertNotified();

    // The group action actually deletes the target model.
    assertModelMissing($group);
    // Messages related to the deleted group are removed through the cascade.
    assertModelMissing($message);
});

test('syncs the group timeline visibility from the schedule field', function (): void {
    $wedding = $this->user->team->wedding;
    $visibleTimeline = WeddingTimeline::factory()->for($wedding)->visible()->create([
        'title' => 'Ceremony',
        'time' => '14:30',
        'sort_order' => 1,
    ]);
    $hiddenTimeline = WeddingTimeline::factory()->for($wedding)->visible()->create([
        'title' => 'Dinner',
        'time' => '18:00',
        'sort_order' => 2,
    ]);
    $unavailableTimeline = WeddingTimeline::factory()->for($wedding)->hidden()->create([
        'title' => 'Private preparation',
        'time' => '12:00',
        'sort_order' => 0,
    ]);
    $group = Group::factory()->for($wedding)->create([
        'invitation_title' => null,
        'invitation_message' => null,
    ]);

    Livewire::test(EditGroup::class, ['record' => $group->getKey()])
        // Only publicly visible schedule items are available in the group form.
        ->assertSchemaComponentVisible('visible_timeline_items')
        ->assertSchemaStateSet([
            'visible_timeline_items' => [$visibleTimeline->getKey(), $hiddenTimeline->getKey()],
        ])
        ->fillForm([
            'visible_timeline_items' => [$visibleTimeline->getKey()],
        ])
        ->call('save')
        // A valid schedule selection is saved without form errors.
        ->assertHasNoFormErrors()
        ->assertNotified();

    $hiddenTimelineIds = $group->refresh()->hiddenTimelineItems()->pluck('wedding_timelines.id')->all();

    // Omitting a schedule item stores it has as hidden for this group.
    expect($hiddenTimelineIds)->toBe([$hiddenTimeline->getKey()])
        // Globally hidden schedule items cannot be injected into the group's visibility pivot.
        ->not->toContain($unavailableTimeline->getKey());
});

test('allows a plus one only for a group with exactly one guest', function (): void {
    $validGroupData = [
        'invitation_title' => null,
        'invitation_message' => null,
    ];
    $emptyGroup = Group::factory()->for($this->user->team->wedding)->create($validGroupData);
    $singleGuestGroup = Group::factory()->for($this->user->team->wedding)->create($validGroupData);
    Guest::factory()->for($singleGuestGroup)->create();
    $multipleGuestGroup = Group::factory()->for($this->user->team->wedding)->create($validGroupData);
    Guest::factory()->count(2)->for($multipleGuestGroup)->create();

    Livewire::test(EditGroup::class, ['record' => $emptyGroup->getKey()])
        // A group without a primary guest cannot enable a plus one.
        ->assertFormFieldDisabled('has_plus_one')
        ->fillForm(['has_plus_one' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    Livewire::test(EditGroup::class, ['record' => $singleGuestGroup->getKey()])
        // Exactly one guest is the only valid condition for the plus-one option.
        ->assertFormFieldEnabled('has_plus_one')
        ->fillForm(['has_plus_one' => true])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    Livewire::test(EditGroup::class, ['record' => $multipleGuestGroup->getKey()])
        // Multiple guests make the plus-one option invalid and prevent forged state from being saved.
        ->assertFormFieldDisabled('has_plus_one')
        ->fillForm(['has_plus_one' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($emptyGroup->refresh()->has_plus_one)->toBeFalse()
        ->and($singleGuestGroup->refresh()->has_plus_one)->toBeTrue()
        ->and($multipleGuestGroup->refresh()->has_plus_one)->toBeFalse();
});

test('shows the empty schedule state when no timeline is defined', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();

    Livewire::test(EditGroup::class, ['record' => $group->getKey()])
        // The form explains that the wedding has no schedule yet.
        ->assertSee(__('wedding.manage_wedding.timeline.not_defined'))
        // The action opens the wedding details page on its schedule tab.
        ->assertSee(ManageWedding::getUrl(['tab' => 'schedule']).'#wedding-timeline')
        // There are no timeline choices to submit when the schedule is empty.
        ->assertFormFieldHidden('visible_timeline_items');
});
