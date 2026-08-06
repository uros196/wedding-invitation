<?php

declare(strict_types=1);

use App\Enums\GuestStatus;
use App\Events\AttendanceConfirmed;
use App\Filament\Wedding\Resources\Groups\Pages\ListGroups;
use App\Filament\Wedding\Resources\Groups\Pages\ViewGroup;
use App\Filament\Wedding\Resources\Groups\RelationManagers\GuestsRelationManager;
use App\Filament\Wedding\Resources\Guests\Pages\ListGuests;
use App\Models\Group;
use App\Models\Guest;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Livewire\Livewire;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

test('filters and bulk deletes groups from the table', function (): void {
    $sentGroup = Group::factory()->for($this->user->team->wedding)->sent()->create();
    $unsentGroup = Group::factory()->for($this->user->team->wedding)->unsent()->create();

    Livewire::test(ListGroups::class)
        ->filterTable('is_sent', true)
        // The Sent filter returns only sent invitations.
        ->assertCanSeeTableRecords([$sentGroup])
        ->assertCanNotSeeTableRecords([$unsentGroup])
        ->callTableBulkAction(DeleteBulkAction::class, [$sentGroup])
        // The bulk action displays a notification and deletes the selected target.
        ->assertNotified();

    // The bulk delete removes the sent group from the database.
    assertModelMissing($sentGroup);
    // The unrelated group remains untouched.
    assertModelExists($unsentGroup);
});

test('refreshes guest tables when attendance status is broadcast', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $guest = Guest::factory()->for($group)->pending()->create();
    $channelName = $this->user->team->broadcastChannelName();
    $expectedListeners = [
        "echo-private:{$channelName},.attendanceConfirmed" => 'refreshTable',
    ];

    $relationManager = Livewire::test(GuestsRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => ViewGroup::class,
    ]);
    $guestList = Livewire::test(ListGuests::class);

    // Both guest tables listen to the private wedding channel for attendance updates.
    expect($relationManager->instance()->getListeners())->toBe($expectedListeners)
        ->and($guestList->instance()->getListeners())->toBe($expectedListeners);

    $guest->update(['status' => GuestStatus::Confirmed]);

    // The listener refreshes each table so the changed status is rendered without a page reload.
    $relationManager
        ->call('refreshTable')
        ->assertDispatched('$refresh')
        ->assertSee(GuestStatus::Confirmed->getLabel());
    $guestList
        ->call('refreshTable')
        ->assertDispatched('$refresh')
        ->assertSee(GuestStatus::Confirmed->getLabel());

    $event = new AttendanceConfirmed($group, [$guest->getKey()]);

    // The broadcast uses the wedding's private channel and exposes only the required identifiers.
    expect($event)
        ->toBeInstanceOf(ShouldBroadcast::class)
        ->and($event->broadcastAs())->toBe('attendanceConfirmed')
        ->and($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class)
        ->and($event->broadcastOn()[0]->name)->toBe("private-{$channelName}")
        ->and($event->broadcastWith())->toBe([
            'group_id' => $group->getKey(),
            'confirmed_ids' => [$guest->getKey()],
        ]);
});

test('lists only groups from the authenticated wedding', function (): void {
    $visibleGroup = Group::factory()->for($this->user->team->wedding)->create([
        'name' => 'Visible Group',
    ]);
    $hiddenGroup = Group::factory()->create(['name' => 'Hidden Group']);

    Livewire::test(ListGroups::class)
        // The user can see groups belonging to their wedding.
        ->assertCanSeeTableRecords([$visibleGroup])
        // Changing the record ID must not expose another wedding's group.
        ->assertCanNotSeeTableRecords([$hiddenGroup]);
});
