<?php

declare(strict_types=1);

use App\Filament\Wedding\Resources\Messages\Pages\ViewMessage;
use App\Models\Group;
use App\Models\Guest;
use App\Models\Message;
use App\Notifications\NewMessageReceived;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('shows message details and marks its notification as read', function (): void {
    $group = Group::factory()->for($this->user->team->wedding)->create([
        'name' => 'Message Group',
    ]);
    $guest = Guest::factory()->for($group)->create([
        'first_name' => 'Ana',
        'last_name' => 'Test',
    ]);
    $message = Message::factory()->for($group)->create([
        'content' => 'A message with important details',
    ]);
    $unrelatedMessage = Message::factory()->for($group)->create();

    $this->user->notify(new NewMessageReceived($group, $message));
    $this->user->notify(new NewMessageReceived($group, $unrelatedMessage));

    expect($this->user->unreadNotifications()->where('data->message_id', $message->getKey())->count())
        ->toBe(1);

    Livewire::test(ViewMessage::class, ['record' => $message->getKey()])
        // The infolist displays the message and its owning group.
        ->assertSchemaStateSet([
            'group.name' => 'Message Group',
            'content' => 'A message with important details',
        ])
        ->assertSee('Message Group')
        ->assertSee('A message with important details')
        ->assertSee($guest->first_name.' '.$guest->last_name)
        // The page provides a link back to the related group.
        ->assertSee(__('View Group'));

    // Opening the message marks only its matching notification as read.
    expect($this->user->fresh()->unreadNotifications()->where('data->message_id', $message->getKey())->count())
        ->toBe(0)
        ->and($this->user->fresh()->unreadNotifications()->where('data->message_id', $unrelatedMessage->getKey())->count())
        ->toBe(1);
});

test('does not load a message belonging to another wedding', function (): void {
    $hiddenMessage = Message::factory()->create();

    // Tenant isolation prevents IDOR access through the record parameter.
    expect(fn (): mixed => Livewire::test(ViewMessage::class, [
        'record' => $hiddenMessage->getKey(),
    ]))->toThrow(ModelNotFoundException::class);
});
