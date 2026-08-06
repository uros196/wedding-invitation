<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\Guest;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a guest can view their personalized invitation with an open RSVP form', function (): void {
    Storage::fake('public');

    $wedding = Wedding::factory()->rsvpOpen()->create([
        'bride_name' => 'Ana',
        'groom_name' => 'Marko',
        'welcome_text' => '<p>Dragi prijatelji, radujemo se vašem dolasku.</p>',
    ]);
    $wedding->addMedia(UploadedFile::fake()->image('hero.jpg'))
        ->toMediaCollection('Hero', 'public');

    $group = Group::factory()->for($wedding)->create([
        'invitation_title' => 'Poruka za naše goste',
        'invitation_message' => 'Jedva čekamo da ovaj dan proslavimo zajedno.',
        'meta_title' => 'Ana i Marko',
    ]);
    Guest::factory()->for($group)->create([
        'first_name' => 'Jelena',
        'last_name' => 'Petrović',
    ]);
    Guest::factory()->for($group)->create([
        'first_name' => 'Nikola',
        'last_name' => 'Jovanović',
    ]);

    $page = $this->visit(route('group.show', ['group' => $group->uuid]));

    $page->assertPathIs('/wedding/'.$group->uuid)
        ->assertTitle('Ana i Marko')
        ->assertSee('Ana')
        ->assertSee('Marko')
        ->assertSee('Dragi prijatelji, radujemo se vašem dolasku.')
        ->assertSee('Poruka za naše goste')
        ->assertSee('Jedva čekamo da ovaj dan proslavimo zajedno.')
        ->assertSee('Potvrda dolaska')
        ->assertSee('Potvrdite dolazak za:')
        ->assertSee('Jelena Petrović')
        ->assertSee('Nikola Jovanović')
        ->assertSee('Poruka za mladence')
        ->assertSee('Potvrdi')
        ->assertNoJavaScriptErrors();
});

test('a guest sees the memory wall instead of the RSVP form after RSVP closes', function (): void {
    Storage::fake('public');

    $wedding = Wedding::factory()->rsvpClosed()->create([
        'bride_name' => 'Ana',
        'groom_name' => 'Marko',
        'has_memory_wall' => true,
    ]);
    $wedding->addMedia(UploadedFile::fake()->image('hero.jpg'))
        ->toMediaCollection('Hero', 'public');

    $group = Group::factory()->for($wedding)->create();

    $page = $this->visit(route('group.show', ['group' => $group->uuid]));

    $page->assertSee('Ana')
        ->assertSee('Marko')
        ->assertSee('Naše uspomene')
        ->assertSee('podelite ih sa nama putem linka ispod.')
        ->assertSee('Podeli slike i snimke')
        ->assertDontSee('Potvrda dolaska')
        ->assertNoJavaScriptErrors();
});
