<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Group;
use App\Models\Guest;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTimeline;
use Inertia\Testing\AssertableInertia as Assert;

test('renders the invitation with only the group-visible wedding data', function (): void {
    $wedding = Wedding::factory()->create();
    $group = Group::factory()->for($wedding)->create([
        'invitation_title' => 'Our wedding',
    ]);
    $guest = Guest::factory()->for($group)->create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
    ]);
    Guest::factory()->for($group)->create();
    $visibleTimeline = WeddingTimeline::factory()->for($wedding)->create([
        'title' => 'Ceremony',
    ]);
    $hiddenTimeline = WeddingTimeline::factory()->for($wedding)->create([
        'title' => 'Private location',
    ]);
    $group->hiddenTimelineItems()->attach($hiddenTimeline, [
        'wedding_id' => $wedding->id,
    ]);

    $response = $this->withHeader('User-Agent', 'Mozilla/5.0')
        ->get(route('group.show', ['group' => $group->uuid]));

    // The invitation is public and returns the expected Inertia page.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitation')
            ->where('group.uuid', $group->uuid)
            ->where('group.invitation_title', 'Our wedding')
            ->where('group.guests_count', 2)
            ->where('group.has_single_guest', false)
            ->where('group.guests.0.id', $guest->id)
            ->where('group.guests.0.full_name', 'Jane Doe')
            ->where('wedding.uuid', $wedding->uuid)
            ->where('wedding.timelines_count', 2)
            ->has('wedding.timelines', 1)
            ->where('wedding.timelines.0.title', $visibleTimeline->title)
        );

    // The counter increases only for a real page view with a normal browser User-Agent.
    expect($group->refresh()->views_count)->toBe(1);
});

test('does not render an invitation while its wedding is a draft', function (): void {
    $wedding = Wedding::factory()->create(['status' => Status::Draft]);
    $group = Group::factory()->for($wedding)->create();

    // Draft invitations must not become public through a previously created group link.
    $this->get(route('group.show', ['group' => $group->uuid]))
        ->assertNotFound();

    expect($group->refresh()->views_count)->toBe(0);
});

test('provides the group metadata used by invitation meta tags', function (): void {
    $wedding = Wedding::factory()->create([
        'meta_title' => 'Wedding metadata title',
        'meta_description' => 'Wedding metadata description',
    ]);
    $group = Group::factory()->for($wedding)->create([
        'meta_title' => 'Invitation metadata title',
        'meta_description' => 'Invitation metadata description',
    ]);

    $response = $this->get(route('group.show', ['group' => $group->uuid]));

    // The invitation page uses these values for its title and Open Graph tags.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitation')
            ->where('metaData.title', 'Invitation metadata title')
            ->where('metaData.description', 'Invitation metadata description')
            ->where('metaData.image', null)
        );
});

test('falls back to wedding metadata when group metadata is not defined', function (): void {
    $wedding = Wedding::factory()->create([
        'meta_title' => 'Wedding fallback title',
        'meta_description' => 'Wedding fallback description',
    ]);
    $group = Group::factory()->for($wedding)->create();

    $response = $this->get(route('group.show', ['group' => $group->uuid]));

    // The invitation should use the wedding metadata when the group has no overrides.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitation')
            ->where('metaData.title', 'Wedding fallback title')
            ->where('metaData.description', 'Wedding fallback description')
            ->where('metaData.image', null)
        );
});

test('falls back to default metadata when group and wedding metadata are not defined', function (): void {
    $wedding = Wedding::factory()->create([
        'meta_title' => null,
        'meta_description' => null,
    ]);
    $group = Group::factory()->for($wedding)->create();

    $response = $this->get(route('group.show', ['group' => $group->uuid]));

    // The invitation should use the configured defaults when no custom metadata exists.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitation')
            ->where('metaData.title', __(config('wedding.meta.title')))
            ->where('metaData.description', __(config('wedding.meta.description')))
            ->where('metaData.image', null)
        );
});

test('does not increase invitation views for automated requests', function (string $userAgent): void {
    $group = Group::factory()->create();

    $this->withHeader('User-Agent', $userAgent)
        ->get(route('group.show', ['group' => $group->uuid]))
        ->assertSuccessful();

    // Bots and requests without a User-Agent must not artificially increase the view count.
    expect($group->refresh()->views_count)->toBe(0);
})->with([
    'crawler' => 'Googlebot/2.1',
    'missing user agent' => '',
]);

test('does not increase invitation views for authenticated wedding panel users', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $wedding = $user->team->wedding;
    $group = Group::factory()->for($wedding)->withViews(7)->create();

    $this->actingAs($user, 'wedding')
        ->withHeader('User-Agent', 'Mozilla/5.0')
        ->get(route('group.show', ['group' => $group->uuid]))
        ->assertSuccessful();

    // Authenticated wedding panel users can preview invitations without inflating public view statistics.
    expect($group->refresh()->views_count)->toBe(7);
});

test('exposes an open RSVP state so the invitation displays the RSVP form', function (): void {
    $wedding = Wedding::factory()->rsvpOpen()->create();
    $group = Group::factory()->for($wedding)->create();
    $guest = Guest::factory()->for($group)->pending()->create();

    $response = $this->withHeader('User-Agent', 'Mozilla/5.0')
        ->get(route('group.show', ['group' => $group->uuid]));

    // The invitation page uses this prop to render the RSVP form for the invited guests.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitation')
            ->where('wedding.is_rsvp_open', true)
            ->where('group.guests.0.id', $guest->id)
        );
});

test('exposes a closed RSVP state so the invitation hides the RSVP form', function (): void {
    $wedding = Wedding::factory()->rsvpClosed()->create();
    $group = Group::factory()->for($wedding)->create();
    $guest = Guest::factory()->for($group)->pending()->create();

    $response = $this->withHeader('User-Agent', 'Mozilla/5.0')
        ->get(route('group.show', ['group' => $group->uuid]));

    // The invitation page uses this prop to hide the RSVP form when confirmations are closed.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitation')
            ->where('wedding.is_rsvp_open', false)
            ->where('group.guests.0.id', $guest->id)
        );
});

test('returns not found when an attacker guesses an unknown group uuid', function (): void {
    // An unknown UUID must not expose data or cause an internal server error.
    $this->get(route('group.show', ['group' => '00000000-0000-4000-8000-000000000000']))
        ->assertNotFound();
});
