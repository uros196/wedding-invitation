<?php

declare(strict_types=1);

use App\Enums\GuestStatus;
use App\Events\AttendanceConfirmed;
use App\Filament\Wedding\Resources\Guests\Pages\ListGuests;
use App\Models\Group;
use App\Models\Guest;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Livewire\Livewire;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

test('lists only guests from the authenticated wedding', function (): void {
    $visibleGroup = Group::factory()->for($this->user->team->wedding)->create();
    $visibleGuest = Guest::factory()->for($visibleGroup)->create([
        'first_name' => 'Visible Guest',
    ]);
    $hiddenGuest = Guest::factory()->create([
        'first_name' => 'Hidden Guest',
    ]);

    Livewire::test(ListGuests::class)
        // The table only exposes guests belonging to the authenticated team.
        ->assertCanSeeTableRecords([$visibleGuest])
        // Replacing an ID or searching for another tenant must not expose the record.
        ->assertCanNotSeeTableRecords([$hiddenGuest]);
});

test('filters guests by attendance status', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $confirmedGuest = Guest::factory()->for($group)->confirmed()->create();
    $pendingGuest = Guest::factory()->for($group)->pending()->create();

    Livewire::test(ListGuests::class)
        ->filterTable('status', GuestStatus::Confirmed->value)
        // The status filter keeps only confirmed guests in the table.
        ->assertCanSeeTableRecords([$confirmedGuest])
        ->assertCanNotSeeTableRecords([$pendingGuest]);
});

test('refreshes the table when attendance status is broadcast', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $guest = Guest::factory()->for($group)->pending()->create();
    $channelName = $this->user->team->broadcastChannelName();
    $component = Livewire::test(ListGuests::class);

    // The table listens on the authenticated wedding's private channel.
    expect($component->instance()->getListeners())->toBe([
        "echo-private:{$channelName},.attendanceConfirmed" => 'refreshTable',
    ]);

    $guest->update(['status' => GuestStatus::Confirmed]);

    // The listener refreshes the table so the changed status is rendered without a page reload.
    $component
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

test('bulk deletes selected guests without affecting unrelated records', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $guestToDelete = Guest::factory()->for($group)->create();
    $unrelatedGuest = Guest::factory()->for($group)->create();

    Livewire::test(ListGuests::class)
        ->callTableBulkAction(DeleteBulkAction::class, [$guestToDelete])
        // The bulk action confirms the deletion through a Filament notification.
        ->assertNotified();

    // Only the selected guest is deleted.
    assertModelMissing($guestToDelete);
    assertModelExists($unrelatedGuest);
});
