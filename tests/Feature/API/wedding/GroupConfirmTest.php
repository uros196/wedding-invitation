<?php

declare(strict_types=1);

use App\Enums\GuestStatus;
use App\Events\AttendanceConfirmed;
use App\Events\MessageReceived;
use App\Models\Group;
use App\Models\Guest;
use App\Models\Message;
use App\Models\User;
use App\Models\Wedding;
use App\Notifications\AttendanceConfirmed as AttendanceConfirmedNotification;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('confirms selected guests, declines the rest, and stores a sanitized message', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $group = Group::factory()->for($wedding)->create();
    $confirmedGuest = Guest::factory()->for($group)->pending()->create();
    $declinedGuest = Guest::factory()->for($group)->pending()->create();
    Event::fake([AttendanceConfirmed::class, MessageReceived::class]);

    $response = $this->from(route('group.show', ['group' => $group->uuid]))
        ->post(route('group.confirm', ['group' => $group->uuid]), [
            'confirmed_guest_ids' => [$confirmedGuest->id],
            'message' => '<b>See you!</b>',
        ]);

    // A successful RSVP redirects to the invitation and stores a flash message.
    $response->assertRedirect(route('group.show', ['group' => $group->uuid]))
        ->assertSessionHas('success', __('wedding.notifications.attendance_confirmation_success'));

    // Only the selected guest remains confirmed; every omitted guest is declined.
    expect($confirmedGuest->refresh()->status)->toBe(GuestStatus::Confirmed)
        ->and($declinedGuest->refresh()->status)->toBe(GuestStatus::Declined);

    // XSS tags are removed before the message is stored while the useful text remains.
    assertDatabaseHas(Message::class, [
        'group_id' => $group->id,
        'content' => 'See you!',
    ]);
    Event::assertDispatched(AttendanceConfirmed::class, fn (AttendanceConfirmed $event): bool => $event->group->is($group) && $event->confirmedIds === [$confirmedGuest->id]
    );
    Event::assertDispatched(MessageReceived::class);
});

test('creates an attendance confirmation notification for wedding administrators', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $admin = User::factory()->for($wedding->team)->create();
    $group = Group::factory()->for($wedding)->create();
    $guest = Guest::factory()->for($group)->pending()->create();

    $this->post(route('group.confirm', ['group' => $group->uuid]), [
        'confirmed_guest_ids' => [$guest->id],
    ])->assertRedirect();

    // A valid RSVP persists one attendance notification for the wedding administrator.
    assertDatabaseHas('notifications', [
        'type' => AttendanceConfirmedNotification::class,
        'notifiable_type' => User::class,
        'notifiable_id' => $admin->id,
    ]);
});

test('creates exactly one confirmed plus-one and closes the plus-one option', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $group = Group::factory()->for($wedding)->withPlusOne()->create();
    $guest = Guest::factory()->for($group)->pending()->create();
    Event::fake([AttendanceConfirmed::class]);

    $this->post(route('group.confirm', ['group' => $group->uuid]), [
        'confirmed_guest_ids' => [$guest->id],
        'plus_one' => ['full_name' => '  Emily   Stone  '],
    ])->assertRedirect();

    // A single RSVP submission must not create more than one additional guest.
    $plusOnes = Guest::query()->where('parent_id', $guest->id)->get();
    expect($plusOnes)->toHaveCount(1)
        ->and($plusOnes->first()->first_name)->toBe('Emily')
        ->and($plusOnes->first()->last_name)->toBe('Stone')
        ->and($plusOnes->first()->status)->toBe(GuestStatus::Confirmed)
        ->and($group->refresh()->has_plus_one)->toBeFalse();
});

test('declines every guest when no guest is confirmed', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $group = Group::factory()->for($wedding)->create();
    $guests = Guest::factory()->for($group)->count(2)->pending()->create();

    $this->post(route('group.confirm', ['group' => $group->uuid]), [])
        ->assertRedirect();

    // An empty selection is valid: every guest who was not selected is declined.
    expect($guests->fresh()->pluck('status')->all())
        ->toBe([GuestStatus::Declined, GuestStatus::Declined]);
});

test('rejects an RSVP when the wedding deadline has passed', function (): void {
    $wedding = Wedding::factory()->rsvpClosed()->create();
    $admin = User::factory()->for($wedding->team)->create();
    $group = Group::factory()
        ->for($wedding)
        ->create();
    $guest = Guest::factory()->for($group)->pending()->create();

    // A closed RSVP must stop the request before any data is changed.
    $this->post(route('group.confirm', ['group' => $group->uuid]), [
        'confirmed_guest_ids' => [$guest->id],
    ])->assertForbidden();

    expect($guest->refresh()->status)->toBe(GuestStatus::Pending);
    assertDatabaseMissing('notifications', [
        'type' => AttendanceConfirmedNotification::class,
        'notifiable_type' => User::class,
        'notifiable_id' => $admin->id,
    ]);
});

test('does not create an attendance confirmation notification for an invalid RSVP', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $admin = User::factory()->for($wedding->team)->create();
    $group = Group::factory()->for($wedding)->create();

    $this->from(route('group.show', ['group' => $group->uuid]))
        ->post(route('group.confirm', ['group' => $group->uuid]), [
            'message' => str_repeat('A', 1001),
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('message');

    // Validation stops the request before the attendance event and notification are dispatched.
    assertDatabaseMissing('notifications', [
        'type' => AttendanceConfirmedNotification::class,
        'notifiable_type' => User::class,
        'notifiable_id' => $admin->id,
    ]);
});

test('throttles repeated RSVP attempts from the same client', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $group = Group::factory()->for($wedding)->create();
    Event::fake([AttendanceConfirmed::class]);

    // The first ten requests in the allowed window pass as normal RSVP attempts.
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->post(route('group.confirm', ['group' => $group->uuid]), [])
            ->assertRedirect();
    }

    // The eleventh attempt must be rejected before reaching the controller or changing data.
    $this->post(route('group.confirm', ['group' => $group->uuid]), [])
        ->assertTooManyRequests();
});

test('rejects guest ids from another group instead of leaking cross-invitation access', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $group = Group::factory()->for($wedding)->create();
    $foreignGroup = Group::factory()->for($wedding)->create();
    $foreignGuest = Guest::factory()->for($foreignGroup)->pending()->create();

    $this->from(route('group.show', ['group' => $group->uuid]))
        ->post(route('group.confirm', ['group' => $group->uuid]), [
            'confirmed_guest_ids' => [$foreignGuest->id],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('confirmed_guest_ids.0');

    // An invalid ID must not change a guest from another group or create an RSVP record.
    expect($foreignGuest->refresh()->status)->toBe(GuestStatus::Pending);
});

test('rejects malformed and overlong attacker input', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $group = Group::factory()->for($wedding)->withPlusOne()->create();

    $this->from(route('group.show', ['group' => $group->uuid]))
        ->post(route('group.confirm', ['group' => $group->uuid]), [
            'message' => str_repeat('A', 1001),
            'plus_one' => ['full_name' => '123'],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['message', 'plus_one.full_name']);

    // The plus-one field is forbidden when the group does not allow it, even with a forged payload.
    $groupWithoutPlusOne = Group::factory()->for($wedding)->withoutPlusOne()->create();
    $this->post(route('group.confirm', ['group' => $groupWithoutPlusOne->uuid]), [
        'plus_one' => ['full_name' => 'Alice Smith'],
    ])->assertSessionHasErrors('plus_one');
});
