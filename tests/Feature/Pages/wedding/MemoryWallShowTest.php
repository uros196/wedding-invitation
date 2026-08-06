<?php

declare(strict_types=1);

use App\Models\Wedding;
use Inertia\Testing\AssertableInertia as Assert;

test('renders the enabled memory wall and its public state', function (): void {
    $wedding = Wedding::factory()->memoryWallEnabled()->create();

    $response = $this->get(route('memory-wall.show', ['wedding' => $wedding->uuid]));

    // The memory wall is public and must return data only for the requested wedding.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memory-wall')
            ->where('wedding.uuid', $wedding->uuid)
            ->where('wedding.has_memory_wall', true)
            ->where('metaData.title', $wedding->meta_title)
            ->has('media', 0)
        );
});

test('provides the wedding metadata used by memory wall meta tags', function (): void {
    $wedding = Wedding::factory()->memoryWallEnabled()->create([
        'meta_title' => 'Memory wall metadata title',
        'meta_description' => 'Memory wall metadata description',
    ]);

    $response = $this->get(route('memory-wall.show', ['wedding' => $wedding->uuid]));

    // The memory wall page uses these values for its title and Open Graph tags.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memory-wall')
            ->where('metaData.title', 'Memory wall metadata title')
            ->where('metaData.description', 'Memory wall metadata description')
            ->where('metaData.image', null)
        );
});

test('falls back to default metadata when wedding metadata is not defined', function (): void {
    $wedding = Wedding::factory()->memoryWallEnabled()->create([
        'meta_title' => null,
        'meta_description' => null,
    ]);

    $response = $this->get(route('memory-wall.show', ['wedding' => $wedding->uuid]));

    // The memory wall should use the configured defaults when no custom metadata exists.
    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memory-wall')
            ->where('metaData.title', __(config('wedding.meta.title')))
            ->where('metaData.description', __(config('wedding.meta.description')))
            ->where('metaData.image', null)
        );
});

test('hides a disabled memory wall instead of exposing wedding details', function (): void {
    $wedding = Wedding::factory()->memoryWallDisabled()->create();

    // A disabled memory wall must be unavailable even when its UUID is valid.
    $this->get(route('memory-wall.show', ['wedding' => $wedding->uuid]))
        ->assertNotFound();
});

test('returns not found for an unknown wedding uuid', function (): void {
    // Guessing an unknown UUID must not reveal whether other wedding data exists.
    $this->get(route('memory-wall.show', [
        'wedding' => '00000000-0000-4000-8000-000000000000',
    ]))->assertNotFound();
});
