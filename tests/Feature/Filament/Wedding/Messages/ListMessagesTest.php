<?php

declare(strict_types=1);

use App\Events\MessageReceived;
use App\Filament\Wedding\Resources\Messages\MessageResource;
use App\Filament\Wedding\Resources\Messages\Pages\ManageMessages;
use App\Models\Group;
use App\Models\Message;
use App\Notifications\NewMessageReceived;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Livewire\Livewire;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

test('lists only messages from the authenticated wedding and supports search', function (): void {
    $visibleGroup = Group::factory()->for($this->user->team->wedding)->create([
        'name' => 'Visible Group',
    ]);
    $visibleMessage = Message::factory()->for($visibleGroup)->create([
        'content' => 'Searchable wedding message',
    ]);
    $otherMessage = Message::factory()->for($visibleGroup)->create([
        'content' => 'Another wedding message',
    ]);
    $hiddenMessage = Message::factory()->create([
        'content' => 'Hidden wedding message',
    ]);

    Livewire::test(ManageMessages::class)
        // The table exposes messages owned by the authenticated wedding.
        ->assertCanSeeTableRecords([$visibleMessage, $otherMessage])
        // Replacing a record ID must not expose another wedding's message.
        ->assertCanNotSeeTableRecords([$hiddenMessage])
        // Search matches the message content while keeping the table scoped.
        ->searchTable('Searchable wedding message')
        ->assertCanSeeTableRecords([$visibleMessage])
        ->assertCanNotSeeTableRecords([$otherMessage, $hiddenMessage]);
});

test('bulk deletes selected messages and preserves unrelated messages', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $deletedMessage = Message::factory()->for($group)->create();
    $preservedMessage = Message::factory()->for($group)->create();

    Livewire::test(ManageMessages::class)
        ->callTableBulkAction(DeleteBulkAction::class, [$deletedMessage])
        // Filament confirms a successful bulk deletion with a notification.
        ->assertNotified();

    // Only the selected message is removed.
    assertModelMissing($deletedMessage);
    assertModelExists($preservedMessage);
});

test('shows the unread message count in the navigation badge', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $message = Message::factory()->for($group)->create();

    $this->user->notify(new NewMessageReceived($group, $message));

    expect(MessageResource::getNavigationBadge())->toBe('1');
});

test('refreshes the table when a message is broadcast', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create();
    $message = Message::factory()->for($group)->create([
        'content' => 'Newly received message',
    ]);
    $channelName = $this->user->team->broadcastChannelName();
    $component = Livewire::test(ManageMessages::class);

    // The table listens on the private channel belonging to the authenticated wedding.
    expect($component->instance()->getListeners())->toBe([
        "echo-private:{$channelName},.messageReceived" => 'refreshTable',
    ]);

    $component
        ->call('refreshTable')
        ->assertDispatched('$refresh')
        ->assertSee($message->content);

    $event = new MessageReceived($message, $group);

    // The broadcast event exposes only the message identifier on the wedding channel.
    expect($event)
        ->toBeInstanceOf(ShouldBroadcast::class)
        ->and($event->broadcastAs())->toBe('messageReceived')
        ->and($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class)
        ->and($event->broadcastOn()[0]->name)->toBe("private-{$channelName}")
        ->and($event->broadcastWith())->toBe([
            'message_id' => $message->getKey(),
        ]);
});
