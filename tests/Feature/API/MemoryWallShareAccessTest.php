<?php

declare(strict_types=1);

use App\Models\MemoryWallShare;
use App\Models\Wedding;

test('rejects an incorrect password without opening the gallery', function (): void {
    $share = MemoryWallShare::factory()
        ->for(Wedding::factory()->memoryWallEnabled())
        ->passwordProtected('correct-password')
        ->create();

    $this->postJson(route('memory-wall.share.unlock', $share), [
        'password' => 'wrong-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');

    $this->get(route('memory-wall.share.show', $share))
        ->assertInertia(fn ($page) => $page
            ->where('requiresPassword', true)
            ->where('media', null)
        );
});

test('stores access only after a valid password and invalidates it after settings change', function (): void {
    $share = MemoryWallShare::factory()
        ->for(Wedding::factory()->memoryWallEnabled())
        ->passwordProtected('correct-password')
        ->create();

    $this->postJson(route('memory-wall.share.unlock', $share), [
        'password' => 'correct-password',
    ])->assertRedirect(route('memory-wall.share.show', $share));

    $this->get(route('memory-wall.share.show', $share))
        ->assertInertia(fn ($page) => $page->where('requiresPassword', false));

    $share->update(['password' => 'new-password']);

    $this->get(route('memory-wall.share.show', $share))
        ->assertInertia(fn ($page) => $page
            ->where('requiresPassword', true)
            ->where('media', null)
        );
});

test('validates the unlock password input', function (): void {
    $share = MemoryWallShare::factory()
        ->for(Wedding::factory()->memoryWallEnabled())
        ->passwordProtected()
        ->create();

    $this->postJson(route('memory-wall.share.unlock', $share), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

test('throttles repeated unlock attempts', function (): void {
    $share = MemoryWallShare::factory()
        ->for(Wedding::factory()->memoryWallEnabled())
        ->passwordProtected()
        ->create();

    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('memory-wall.share.unlock', $share), [
            'password' => "wrong-password-{$attempt}",
        ])->assertUnprocessable();
    }

    $this->postJson(route('memory-wall.share.unlock', $share), [
        'password' => 'wrong-password-final',
    ])->assertTooManyRequests();
});
