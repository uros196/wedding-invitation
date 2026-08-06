<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Age;
use App\Enums\FilamentPanel;
use App\Enums\Gender;
use App\Enums\GuestStatus;
use App\Enums\TeamType;
use App\Enums\UserType;
use App\Models\Group;
use App\Models\Guest;
use App\Models\Message;
use App\Models\Team;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTimeline;
use App\Notifications\AttendanceConfirmed;
use App\Notifications\NewMessageReceived;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class WeddingPanelSeeder extends Seeder
{
    private const string TEAM_ACCOUNT_EMAIL = 'wedding@example.com';

    private const string TEAM_ACCOUNT_PASSWORD = 'password';

    private const int ADDITIONAL_GROUP_COUNT = 18;

    private const int MIN_GUESTS_PER_ADDITIONAL_GROUP = 1;

    private const int MAX_GUESTS_PER_ADDITIONAL_GROUP = 6;

    /**
     * Public, stable image URLs used only for local development data.
     *
     * @var array<string, string>
     */
    private const array IMAGE_URLS = [
        'hero' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=1600&q=80',
        'meta' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1200&q=80',
        'memory_one' => 'https://images.unsplash.com/photo-1507504031003-b417219a0fde?w=1200&q=80',
        'memory_two' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=1200&q=80',
        'group_one' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=1000&q=80',
        'group_two' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=1000&q=80',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the team, wedding and the account used to access the panel.
        $this->progress('team');
        $team = $this->createTeam();

        $this->progress('wedding');
        $wedding = $this->createWedding($team);

        $this->progress('account');
        $user = $this->createTeamMember($team);

        // Fill the wedding configuration and both visible and hidden schedule items.
        $this->progress('timelines');
        $timelines = $this->createTimelines($wedding);

        // Create invitation groups with different invitation and metadata states.
        $this->progress('groups');
        $groups = $this->createGroups($wedding);
        $this->progress('groups_created', ['count' => count($groups)]);

        // Create every guest status, age and gender, including companions.
        $this->progress('guests');
        $guestCount = $this->createGuests($groups);
        $this->progress('guests_created', ['count' => $guestCount]);

        // Add messages that will be displayed in the panel's message resource.
        $this->progress('messages');
        $messageCount = $this->createMessages($groups);
        $this->progress('messages_created', ['count' => $messageCount]);

        // Demonstrate per-group visibility settings for timeline items.
        $this->progress('visibility');
        $this->hideTimelineItems($groups, $timelines, $wedding);

        // Add one read and one unread database notification for the panel user.
        $this->progress('notifications');
        $attendanceNotificationCount = $this->createNotifications($user, $groups);
        $this->progress('notifications_created', ['count' => $attendanceNotificationCount]);

        // Download public development images and store them in the configured media collections.
        $this->progress('images');
        $this->attachImages($wedding, $groups);

        $this->progress('finished');

        $this->command->newLine();
        $this->command->warn(__('messages.dev_seed.panel_credentials', [
            'panel' => FilamentPanel::Wedding->value,
            'email' => self::TEAM_ACCOUNT_EMAIL,
            'password' => self::TEAM_ACCOUNT_PASSWORD,
        ]));
        $this->command->newLine();
    }

    /**
     * Create the team that owns all wedding data.
     */
    private function createTeam(): Team
    {
        return Team::factory()->create([
            'name' => 'Demo Wedding Team',
            'has_memory_wall' => true,
            'type' => TeamType::Wedding,
        ]);
    }

    /**
     * Create a wedding with every configurable top-level option populated.
     */
    private function createWedding(Team $team): Wedding
    {
        $weddingDay = now()->addMonths(3)->startOfDay();

        return Wedding::factory()->for($team)->rsvpOpen()->create([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => $weddingDay,
            'welcome_text' => 'Dobro došli na našu proslavu ljubavi i zajedničkog života.',
            'has_memory_wall' => true,
            'memory_wall_open_until' => $weddingDay->addDays(config('wedding.invitation.memory_wall.form_open_for_max')),
            'meta_title' => 'Ana i Marko - venčanje',
            'meta_description' => 'Sve informacije o venčanju Ane i Marka na jednom mestu.',
        ]);
    }

    /**
     * Create the generic account used to access the wedding panel in development.
     */
    private function createTeamMember(Team $team): User
    {
        return User::query()->create([
            'name' => 'Wedding Demo User',
            'email' => self::TEAM_ACCOUNT_EMAIL,
            'password' => self::TEAM_ACCOUNT_PASSWORD,
            'user_type' => UserType::TeamMember,
            'team_id' => $team->getKey(),
            'locale' => 'sr_Latn',
        ]);
    }

    /**
     * Create schedule entries that cover visible and hidden timeline states.
     *
     * @return array<string, WeddingTimeline>
     */
    private function createTimelines(Wedding $wedding): array
    {
        $timelines = [
            'ceremony' => WeddingTimeline::factory()->for($wedding)->create([
                'title' => 'Ceremonija',
                'address' => 'Hram Svetog Save, Beograd',
                'time' => '16:00',
                'map_url' => 'https://maps.google.com/?q=Hram+Svetog+Save',
                'is_visible' => true,
                'sort_order' => 1,
            ]),
            'reception' => WeddingTimeline::factory()->for($wedding)->create([
                'title' => 'Svadbeno veselje',
                'address' => 'Restoran Kalem, Beograd',
                'time' => '18:00',
                'map_url' => 'https://maps.google.com/?q=Beograd',
                'is_visible' => true,
                'sort_order' => 2,
            ]),
            'party' => WeddingTimeline::factory()->for($wedding)->create([
                'title' => 'Večernja zabava',
                'address' => 'Restoran Kalem, Beograd',
                'time' => '22:00',
                'map_url' => 'https://maps.google.com/?q=Beograd',
                'is_visible' => false,
                'sort_order' => 3,
            ]),
        ];

        // Add Faker-generated entries so the timeline contains more realistic development data.
        $timelines['additional_one'] = WeddingTimeline::factory()
            ->for($wedding)
            ->visible()
            ->atSortOrder(4)
            ->create();
        $timelines['additional_two'] = WeddingTimeline::factory()
            ->for($wedding)
            ->hidden()
            ->atSortOrder(5)
            ->create();

        return $timelines;
    }

    /**
     * Create groups with sent/unsent, plus-one, views and metadata variations.
     *
     * @return array<string, Group>
     */
    private function createGroups(Wedding $wedding): array
    {
        $groups = [
            'family' => Group::factory()->for($wedding)->sent()->withViews(18)->withMeta()->create([
                'name' => 'Porodica Petrović',
                'invitation_title' => 'Dragi Petrovići, vidimo se na našem venčanju!',
            ]),
            'friends' => Group::factory()->for($wedding)->sent()->withViews(7)->create([
                'name' => 'Prijatelji',
            ]),
            'colleagues' => Group::factory()->for($wedding)->unsent()->create([
                'name' => 'Kolege',
                'meta_title' => 'Informacije za kolege',
                'meta_description' => 'Personalizovane informacije za grupu kolega.',
            ]),
            'single_friend' => Group::factory()->for($wedding)->sent()->create([
                'name' => 'Prijatelj iz detinjstva',
            ]),
        ];

        // Let the configured Serbian Faker locale generate additional group names and content.
        $additionalGroups = Group::factory()
            ->for($wedding)
            ->count(self::ADDITIONAL_GROUP_COUNT)
            ->withMeta()
            ->state(fn (): array => [
                'is_sent' => fake()->boolean(75),
                'views_count' => fake()->numberBetween(0, 80),
            ])
            ->create();

        foreach ($additionalGroups as $index => $group) {
            $groups["generated_{$index}"] = $group;
        }

        return $groups;
    }

    /**
     * Create guests covering all enum values and the companion relationship.
     *
     * @param  array<string, Group>  $groups
     */
    private function createGuests(array $groups): int
    {
        $createdGuests = 0;

        $parent = Guest::factory()->for($groups['family'])->create([
            'first_name' => 'Jelena',
            'last_name' => 'Petrović',
            'status' => GuestStatus::Confirmed,
            'age' => Age::Adult,
            'gender' => Gender::Female,
        ]);
        $createdGuests++;

        Guest::factory()->companionOf($parent)->create([
            'first_name' => 'Nikola',
            'last_name' => 'Petrović',
            'status' => GuestStatus::Confirmed,
            'age' => Age::Adult,
            'gender' => Gender::Male,
        ]);
        $createdGuests++;

        Guest::factory()->for($groups['family'])->pending()->child()->female()->create([
            'first_name' => 'Lena',
            'last_name' => 'Petrović',
        ]);
        $createdGuests++;

        Guest::factory()->for($groups['friends'])->declined()->baby()->male()->create([
            'first_name' => 'Vuk',
            'last_name' => 'Jovanović',
        ]);
        $createdGuests++;

        Guest::factory()->for($groups['friends'])->pending()->adult()->male()->create([
            'first_name' => 'Stefan',
            'last_name' => 'Jovanović',
        ]);
        $createdGuests++;

        Guest::factory()->for($groups['colleagues'])->confirmed()->adult()->female()->create([
            'first_name' => 'Milica',
            'last_name' => 'Nikolić',
        ]);
        $createdGuests++;

        Guest::factory()->for($groups['colleagues'])->declined()->child()->female()->create([
            'first_name' => 'Sara',
            'last_name' => 'Nikolić',
        ]);
        $createdGuests++;

        Guest::factory()->for($groups['single_friend'])->pending()->adult()->male()->create([
            'first_name' => 'Ilija',
            'last_name' => 'Jovanović',
        ]);
        $createdGuests++;
        $this->setRandomPlusOneForSingleGuest($groups['single_friend']);

        // Let Faker generate realistic guest combinations for every additional group.
        foreach ($groups as $key => $group) {
            if (! Str::startsWith($key, 'generated_')) {
                continue;
            }

            $guestCount = fake()->numberBetween(
                self::MIN_GUESTS_PER_ADDITIONAL_GROUP,
                self::MAX_GUESTS_PER_ADDITIONAL_GROUP,
            );

            Guest::factory()
                ->for($group)
                ->count($guestCount)
                ->state(fn (): array => [
                    'status' => fake()->randomElement(GuestStatus::cases()),
                    'age' => fake()->randomElement(Age::cases()),
                    'gender' => fake()->randomElement(Gender::cases()),
                ])
                ->create();

            $createdGuests += $guestCount;
            $this->setRandomPlusOneForSingleGuest($group);

            if (! $group->has_plus_one || ! fake()->boolean()) {
                continue;
            }

            /** @var Guest|null $companionParent */
            $companionParent = $group->guests()->first();

            if (! $companionParent) {
                continue;
            }

            Guest::factory()->companionOf($companionParent)->create([
                'status' => fake()->randomElement(GuestStatus::cases()),
                'age' => fake()->randomElement(Age::cases()),
                'gender' => fake()->randomElement(Gender::cases()),
            ]);
            $createdGuests++;
            $group->update(['has_plus_one' => false]);
        }

        return $createdGuests;
    }

    /**
     * Randomly enable the plus-one option only for groups with one guest.
     */
    private function setRandomPlusOneForSingleGuest(Group $group): void
    {
        $group->update([
            'has_plus_one' => $group->hasOnlyOneGuest() && fake()->boolean(),
        ]);
    }

    /**
     * Create messages for different invitation groups.
     *
     * @param  array<string, Group>  $groups
     */
    private function createMessages(array $groups): int
    {
        $customMessages = [
            'family' => 'Jedva čekamo da vas vidimo i zajedno proslavimo ovaj dan!',
            'friends' => 'Molimo vas da nam javite ako imate neka pitanja oko dolaska.',
            'colleagues' => 'Hvala na pozivu, radujemo se proslavi.',
        ];
        $createdMessages = 0;

        foreach ($groups as $key => $group) {
            $messageCount = Str::startsWith($key, 'generated_')
                ? fake()->numberBetween(1, 3)
                : 1;

            for ($messageIndex = 0; $messageIndex < $messageCount; $messageIndex++) {
                $attributes = [];

                if ($messageIndex === 0 && isset($customMessages[$key])) {
                    $attributes['content'] = $customMessages[$key];
                }

                Message::factory()->for($group)->create($attributes);
                $createdMessages++;
            }
        }

        return $createdMessages;
    }

    /**
     * Hide selected timeline items for one group while keeping them visible to others.
     *
     * @param  array<string, Group>  $groups
     * @param  array<string, WeddingTimeline>  $timelines
     */
    private function hideTimelineItems(array $groups, array $timelines, Wedding $wedding): void
    {
        $groups['friends']->hiddenTimelineItems()->attach($timelines['party']->getKey(), [
            'wedding_id' => $wedding->getKey(),
        ]);
    }

    /**
     * Create database notifications and mark the message notification as read.
     *
     * @param  array<string, Group>  $groups
     */
    private function createNotifications(User $user, array $groups): int
    {
        /** @var Message|null $familyMessage */
        $familyMessage = $groups['family']->messages()->first();

        if ($familyMessage) {
            $user->notify(new NewMessageReceived($groups['family'], $familyMessage));
        }

        /** @var array<int, array{group: Group, confirmed: int, total: int}> $eligibleGroups */
        $eligibleGroups = [];

        foreach ($groups as $group) {
            if (! $group->is_sent) {
                continue;
            }

            $group->loadMissing(['guests', 'messages']);
            $totalGuests = $group->guests->count();
            $confirmedGuests = $group->guests
                ->where('status', GuestStatus::Confirmed)
                ->count();
            $messageCount = $group->messages->count();
            $confirmedPercentage = $totalGuests > 0
                ? ($confirmedGuests / $totalGuests) * 100
                : 0;

            if ($confirmedPercentage <= $messageCount || $confirmedGuests === 0) {
                continue;
            }

            $eligibleGroups[] = [
                'group' => $group,
                'confirmed' => $confirmedGuests,
                'total' => $totalGuests,
            ];
        }

        // Randomly select eligible sent groups while guaranteeing one useful example.
        $selectedGroups = array_values(array_filter(
            $eligibleGroups,
            fn (): bool => fake()->boolean(60),
        ));

        if ($selectedGroups === [] && $eligibleGroups !== []) {
            $selectedGroups[] = $eligibleGroups[array_key_first($eligibleGroups)];
        }

        foreach ($selectedGroups as $selectedGroup) {
            $user->notify(new AttendanceConfirmed(
                $selectedGroup['group'],
                $selectedGroup['confirmed'],
                $selectedGroup['total'],
            ));
        }

        if ($familyMessage) {
            $user->notifications()
                ->where('type', NewMessageReceived::class)
                ->latest()
                ->first()
                ?->markAsRead();
        }

        return count($selectedGroups);
    }

    /**
     * Attach all development images to the collections used by the wedding panel.
     *
     * @param  array<string, Group>  $groups
     */
    private function attachImages(Wedding $wedding, array $groups): void
    {
        $this->downloadAndAttachImage(
            $wedding,
            'Hero',
            self::IMAGE_URLS['hero'],
            'wedding-hero.jpg',
        );
        $this->downloadAndAttachImage(
            $wedding,
            'MetaImage',
            self::IMAGE_URLS['meta'],
            'wedding-meta.jpg',
        );
        $this->downloadAndAttachImage(
            $wedding,
            'MemoryWall',
            self::IMAGE_URLS['memory_one'],
            'memory-wall-one.jpg',
        );
        $this->downloadAndAttachImage(
            $wedding,
            'MemoryWall',
            self::IMAGE_URLS['memory_two'],
            'memory-wall-two.jpg',
        );
        $this->downloadAndAttachImage(
            $groups['family'],
            'MetaImage',
            self::IMAGE_URLS['group_one'],
            'family-meta.jpg',
        );
        $this->downloadAndAttachImage(
            $groups['friends'],
            'MetaImage',
            self::IMAGE_URLS['group_two'],
            'friends-meta.jpg',
        );
    }

    /**
     * Download an image with bounded retries and store it in a media collection.
     */
    private function downloadAndAttachImage(
        Wedding|Group $model,
        string $collection,
        string $url,
        string $fileName,
    ): void {
        $response = Http::accept('image/*')
            ->timeout(15)
            ->connectTimeout(5)
            ->retry(2, 100)
            ->get($url)
            ->throw();

        $contentType = (string) $response->header('Content-Type');

        if (! Str::startsWith($contentType, 'image/')) {
            throw new RuntimeException("Development image URL [{$url}] did not return an image.");
        }

        $model->addMediaFromString($response->body())
            ->usingFileName($fileName)
            ->toMediaCollection($collection);
    }

    /**
     * Display a translated progress message in the parent Artisan command.
     *
     * @param  array<string, mixed>  $replace
     */
    private function progress(string $key, array $replace = []): void
    {
        $this->command->line(__('messages.dev_seed.progress.'.$key, $replace));
    }
}
