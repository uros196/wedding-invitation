<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Filament\Wedding\Pages\Dashboard;
use App\Filament\Wedding\Pages\ManageWedding\ManageWedding;
use App\Filament\Wedding\Pages\SetupWedding\SetupWedding;
use App\Models\Group;
use App\Models\Team;
use App\Models\User;
use App\Models\WeddingTimeline;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelExists;

test('displays all populated wedding detail sections', function (): void {
    $wedding = $this->user->team->wedding;
    $wedding->update([
        'bride_name' => 'Ana',
        'groom_name' => 'Marko',
        'wedding_date' => '2027-07-10',
        'rsvp_deadline' => '2027-06-30 18:00:00',
        'welcome_text' => '<p>Welcome to our wedding.</p>',
        'has_memory_wall' => true,
        'memory_wall_open_until' => '2027-07-20 23:59:59',
        'meta_title' => 'Ana and Marko',
        'meta_description' => 'Join us for our special day.',
    ]);
    $timeline = WeddingTimeline::factory()->for($wedding)->create([
        'title' => 'Ceremony',
        'address' => 'Church of Saint Sava',
        'time' => '16:00',
        'map_url' => 'https://maps.example.test/ceremony',
        'sort_order' => 1,
    ]);

    Livewire::test(ManageWedding::class)
        // The page hydrates every persisted scalar section into the form.
        ->assertSchemaStateSet([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ])
        // The populated schedule is rendered and the memory-wall-only fields are available.
        ->set('activeTab', 'appearance')
        ->assertSchemaStateSet(['welcome_text' => '<p>Welcome to our wedding.</p>'])
        ->assertFormFieldExists('Hero')
        ->assertFormFieldExists('welcome_text')
        ->set('activeTab', 'schedule')
        ->assertSchemaComponentExists('timelines')
        ->assertSee($timeline->title)
        ->set('activeTab', 'memory')
        ->assertSchemaStateSet([
            'has_memory_wall' => true,
            'memory_wall_open_until' => '2027-07-20 23:59:59',
        ])
        ->assertFormFieldVisible('has_memory_wall')
        ->assertFormFieldVisible('memory_wall_open_until')
        ->assertFormFieldVisible('memory_wall_url')
        ->assertSchemaComponentVisible('memory_wall_qr_code')
        ->set('activeTab', 'meta')
        ->assertSchemaStateSet([
            'meta_title' => 'Ana and Marko',
            'meta_description' => 'Join us for our special day.',
        ])
        ->assertFormFieldVisible('meta_title')
        ->assertFormFieldVisible('meta_description')
        ->assertFormFieldVisible('MetaImage');
});

test('returns to the tab containing the first validation error', function (): void {
    Livewire::test(ManageWedding::class)
        ->set('activeTab', 'meta')
        ->fillForm([
            'bride_name' => null,
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00:00',
            'welcome_text' => '<p>Welcome to our wedding.</p>',
        ])
        ->call('save')
        ->assertHasFormErrors(['bride_name' => 'required'])
        ->assertSet('activeTab', 'basic');
});

test('does not expose wedding details while the wedding is a draft', function (): void {
    $this->user->team->wedding->update(['status' => Status::Draft]);

    $this->get(ManageWedding::getUrl())->assertForbidden();
});

test('limits timeline visibility changes to groups in the timeline wedding', function (): void {
    $wedding = $this->user->team->wedding;
    $visibleGroup = Group::factory()->for($wedding)->create(['name' => 'Visible Group']);
    $hiddenGroup = Group::factory()->for($wedding)->create(['name' => 'Hidden Group']);
    $foreignGroup = Group::factory()->create(['name' => 'Foreign Group']);
    $timeline = WeddingTimeline::factory()->for($wedding)->create([
        'title' => 'Ceremony',
        'time' => '16:00',
    ]);
    $timeline->hiddenByGroups()->syncWithPivotValues([$hiddenGroup->getKey()], [
        'wedding_id' => $wedding->getKey(),
    ]);

    Livewire::test(ManageWedding::class)
        ->set('activeTab', 'schedule')
        ->callFormComponentAction(
            'timelines',
            'manage_visibility',
            ['visible_group_ids' => [$visibleGroup->getKey()]],
            ['item' => "record-{$timeline->getKey()}"],
        )
        ->assertHasNoFormErrors();

    // A foreign group ID is rejected by the action schema before persistence.
    Livewire::test(ManageWedding::class)
        ->set('activeTab', 'schedule')
        ->callFormComponentAction(
            'timelines',
            'manage_visibility',
            ['visible_group_ids' => [$visibleGroup->getKey(), $foreignGroup->getKey()]],
            ['item' => "record-{$timeline->getKey()}"],
        )
        ->assertHasErrors(['mountedActions.0.data.visible_group_ids.1']);

    assertDatabaseHas('group_hidden_timeline_items', [
        'group_id' => $hiddenGroup->getKey(),
        'wedding_timeline_id' => $timeline->getKey(),
        'wedding_id' => $wedding->getKey(),
    ]);
    assertDatabaseMissing('group_hidden_timeline_items', [
        'group_id' => $visibleGroup->getKey(),
        'wedding_timeline_id' => $timeline->getKey(),
    ]);
    assertDatabaseMissing('group_hidden_timeline_items', [
        'group_id' => $foreignGroup->getKey(),
        'wedding_timeline_id' => $timeline->getKey(),
    ]);
});

test('shows the empty schedule state when no timeline exists', function (): void {
    $wedding = $this->user->team->wedding;
    $wedding->timelines()->delete();

    Livewire::test(ManageWedding::class)
        ->set('activeTab', 3)
        // An empty schedule explains how to start defining the wedding day.
        ->assertSee(__('wedding.manage_wedding.timeline.not_defined'));

    $timeline = WeddingTimeline::factory()->for($wedding)->create([
        'title' => 'Reception',
        'time' => '18:00',
    ]);

    Livewire::test(ManageWedding::class)
        ->set('activeTab', 3)
        // The empty state disappears as soon as the relationship has an item.
        ->assertDontSee(__('wedding.manage_wedding.timeline.not_defined'))
        ->assertSee($timeline->title);
});

test('hides memory wall controls when the team does not have access', function (): void {
    $this->user->team->update(['has_memory_wall' => false]);

    Livewire::test(ManageWedding::class)
        // The policy-controlled section must not expose any memory wall control.
        ->assertDontSee(__('Memory Wall'))
        // Meta data remains available when the optional section is removed.
        ->set('activeTab', 5)
        ->assertFormFieldVisible('meta_title')
        ->assertFormFieldVisible('meta_description');
});

test('disables dependent memory wall fields when the wall is turned off', function (): void {
    Livewire::test(ManageWedding::class)
        ->set('activeTab', 'memory')
        ->fillForm(['has_memory_wall' => false])
        // The date cannot be changed while memory-wall submissions are disabled.
        ->assertFormFieldDisabled('memory_wall_open_until')
        // URL and QR code are not meaningful without an enabled wall.
        ->assertFormFieldHidden('memory_wall_url')
        ->assertSchemaComponentHidden('memory_wall_qr_code');
});

test('does not expose wedding details when the team has no wedding', function (): void {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->getKey()]);
    $this->actingAs($user, 'wedding');

    $this->get(ManageWedding::getUrl())->assertForbidden();
});

test('creates the first wedding through the setup wizard', function (): void {
    Storage::fake('public');
    $team = $this->user->team;
    $team->wedding()->delete();
    $this->user->unsetRelation('team');

    $component = Livewire::test(SetupWedding::class)
        ->fillForm([
            'Hero' => [UploadedFile::fake()->image('first-hero.jpg', 800, 1000)],
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00',
            'welcome_text' => '<p>Welcome to our wedding.</p>',
            'has_memory_wall' => false,
        ])
        ->call('publish');

    $component->assertRedirect(Dashboard::getUrl());

    $wedding = $team->refresh()->wedding;

    // First-time setup creates the wedding under the authenticated team.
    expect($wedding)->not->toBeNull()
        ->and($wedding->bride_name)->toBe('Ana')
        ->and($wedding->status)->toBe(Status::Published)
        ->and($wedding->getFirstMedia('Hero'))->not->toBeNull();
});

test('updates and removes existing timeline items', function (): void {
    Storage::fake('public');
    $wedding = $this->user->team->wedding;
    $wedding->addMedia(UploadedFile::fake()->image('hero.jpg', 800, 1000))
        ->toMediaCollection('Hero', 'public');
    $updatedTimeline = WeddingTimeline::factory()->for($wedding)->create([
        'title' => 'Ceremony',
        'time' => '16:00',
        'sort_order' => 1,
    ]);
    $deletedTimeline = WeddingTimeline::factory()->for($wedding)->create([
        'title' => 'Reception',
        'time' => '18:00',
        'sort_order' => 2,
    ]);

    Repeater::fake();

    Livewire::test(ManageWedding::class)
        ->fillForm([
            'meta_title' => 'Ana and Marko',
            'meta_description' => 'Join us on our special day.',
        ])
        ->set('data.timelines', [
            "record-{$updatedTimeline->getKey()}" => [
                'id' => $updatedTimeline->getKey(),
                'wedding_id' => $wedding->getKey(),
                'title' => 'Updated Ceremony',
                'time' => '16:30',
                'address' => 'Updated address',
                'map_url' => 'https://maps.example.test/updated-ceremony',
                'is_visible' => false,
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    // Existing repeater records are updated by ID and omitted records are deleted.
    expect($updatedTimeline->refresh()->title)->toBe('Updated Ceremony')
        ->and($updatedTimeline->time->format('H:i'))->toBe('16:30')
        ->and($updatedTimeline->is_visible)->toBeFalse();

    expect($wedding->timelines()->whereKey($deletedTimeline->getKey())->exists())->toBeFalse();
});

test('validates the required hero and timeline fields', function (): void {
    Livewire::test(ManageWedding::class)
        ->fillForm([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00',
            'welcome_text' => 'Welcome to our wedding.',
            'has_memory_wall' => false,
            'timelines' => [
                [
                    'title' => '',
                    'time' => '',
                    'map_url' => 'not-a-url',
                ],
            ],
        ])
        ->call('save')
        // Required media and nested timeline fields must be validated server-side.
        ->assertHasFormErrors([
            'Hero',
            'timelines.0.title',
            'timelines.0.time',
            'timelines.0.map_url',
        ])
        ->assertNotNotified();
});

test('validates timeline text and link length limits', function (array $timeline, string $error): void {
    Livewire::test(ManageWedding::class)
        ->fillForm([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00',
            'welcome_text' => 'Welcome to our wedding.',
            'has_memory_wall' => false,
            'timelines' => [$timeline],
        ])
        ->call('save')
        // Timeline limits protect both rendered labels and persisted links.
        ->assertHasFormErrors([$error])
        ->assertNotNotified();
})->with([
    'event title' => [
        [
            'title' => Str::repeat('a', 101),
            'time' => '16:00',
            'address' => 'Church',
            'map_url' => 'https://maps.example.test/ceremony',
        ],
        'timelines.0.title',
    ],
    'event address' => [
        [
            'title' => 'Ceremony',
            'time' => '16:00',
            'address' => Str::repeat('a', 101),
            'map_url' => 'https://maps.example.test/ceremony',
        ],
        'timelines.0.address',
    ],
    'map link' => [
        [
            'title' => 'Ceremony',
            'time' => '16:00',
            'address' => 'Church',
            'map_url' => 'https://maps.example.test/'.Str::repeat('a', 240),
        ],
        'timelines.0.map_url',
    ],
]);

test('validates required fields and configured length limits', function (array $overrides, array $errors): void {
    Livewire::test(ManageWedding::class)
        ->fillForm([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00',
            'welcome_text' => 'Welcome to our wedding.',
            'has_memory_wall' => false,
            ...$overrides,
        ])
        ->call('save')
        // Invalid input must be rejected without a success notification.
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    'bride name is required' => [
        ['bride_name' => null],
        ['bride_name'],
    ],
    'groom name is required' => [
        ['groom_name' => null],
        ['groom_name'],
    ],
    'wedding date is required' => [
        ['wedding_date' => null],
        ['wedding_date'],
    ],
    'welcome text is required' => [
        ['welcome_text' => ''],
        ['welcome_text'],
    ],
    'bride name cannot exceed fifty characters' => [
        ['bride_name' => Str::repeat('a', 51)],
        ['bride_name'],
    ],
    'groom name cannot exceed fifty characters' => [
        ['groom_name' => Str::repeat('a', 51)],
        ['groom_name'],
    ],
    'meta title cannot exceed sixty characters' => [
        ['meta_title' => Str::repeat('a', 61)],
        ['meta_title'],
    ],
    'meta description cannot exceed one hundred fifty characters' => [
        ['meta_description' => Str::repeat('a', 151)],
        ['meta_description'],
    ],
]);

test('rejects dates outside their configured chronological boundaries', function (array $overrides, array $errors): void {
    Livewire::test(ManageWedding::class)
        ->fillForm([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00',
            'welcome_text' => 'Welcome to our wedding.',
            'has_memory_wall' => true,
            ...$overrides,
        ])
        ->call('save')
        // Date constraints are enforced server-side, not only by the date picker UI.
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    'rsvp deadline after wedding date' => [
        ['rsvp_deadline' => '2027-07-11 00:00'],
        ['rsvp_deadline'],
    ],
    'memory wall closes before the wedding' => [
        ['memory_wall_open_until' => '2027-07-09 23:59'],
        ['memory_wall_open_until'],
    ],
    'memory wall exceeds the allowed period' => [
        ['memory_wall_open_until' => '2027-07-21 00:00'],
        ['memory_wall_open_until'],
    ],
]);

test('saves wedding details and timeline items for the authenticated team', function (): void {
    Storage::fake('public');
    $wedding = $this->user->team->wedding;
    $wedding->addMedia(UploadedFile::fake()->image('hero.jpg', 1200, 800))
        ->toMediaCollection('Hero', 'public');

    Repeater::fake();

    Livewire::test(ManageWedding::class)
        ->fillForm([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00',
            'welcome_text' => '<p>We are happy to celebrate with you.</p>',
            'has_memory_wall' => true,
            'memory_wall_open_until' => '2027-07-20 23:59',
            'meta_title' => 'Ana & Marko',
            'meta_description' => 'Join us on our special day.',
            'timelines' => [
                [
                    'title' => 'Ceremony',
                    'time' => '16:00',
                    'address' => 'Church of Saint Sava',
                    'map_url' => 'https://maps.example.test/ceremony',
                    'is_visible' => true,
                ],
                [
                    'title' => 'Reception',
                    'time' => '18:00',
                    'address' => 'Belgrade Waterfront',
                    'map_url' => 'https://maps.example.test/reception',
                    'is_visible' => false,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $wedding->refresh();

    // Every editable scalar value is persisted on the current wedding.
    expect($wedding->bride_name)->toBe('Ana')
        ->and($wedding->groom_name)->toBe('Marko')
        ->and($wedding->welcome_text)->toBe('<p>We are happy to celebrate with you.</p>')
        ->and($wedding->has_memory_wall)->toBeTrue()
        ->and($wedding->meta_title)->toBe('Ana & Marko')
        ->and($wedding->meta_description)->toBe('Join us on our special day.');

    // The relationship repeater creates ordered timeline records for this wedding.
    $timelines = $wedding->timelines()->orderBy('sort_order')->get();

    expect($timelines)->toHaveCount(2)
        ->and($timelines[0]->title)->toBe('Ceremony')
        ->and($timelines[0]->is_visible)->toBeTrue()
        ->and($timelines[1]->title)->toBe('Reception')
        ->and($timelines[1]->is_visible)->toBeFalse();

    assertModelExists($wedding);
});

test('rejects a non-chronological timeline', function (): void {
    Livewire::test(ManageWedding::class)
        ->fillForm([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00',
            'welcome_text' => 'Welcome to our wedding.',
            'has_memory_wall' => false,
            'timelines' => [
                ['title' => 'Reception', 'time' => '18:00', 'is_visible' => true],
                ['title' => 'Ceremony', 'time' => '16:00', 'is_visible' => true],
            ],
        ])
        ->call('save')
        // Timeline events must remain in chronological order.
        ->assertHasFormErrors(['timelines'])
        ->assertNotNotified();
});
