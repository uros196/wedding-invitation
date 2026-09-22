<?php

declare(strict_types=1);

use App\Filament\Wedding\Resources\MemoryWallShares\Pages\CreateMemoryWallShare;
use App\Models\MemoryWallShare;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseCount;

test('creates a share link for the authenticated wedding with its access settings', function (): void {
    $otherWedding = MemoryWallShare::factory()->create()->wedding;

    Livewire::test(CreateMemoryWallShare::class)
        ->fillForm([
            'name' => 'Guests gallery',
            'password' => 'secret-password',
            'expires_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'allow_downloads' => true,
            'wedding_id' => $otherWedding->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $share = MemoryWallShare::query()->where('name', 'Guests gallery')->sole();

    expect($share->wedding_id)->toBe($this->user->team->wedding->getKey())
        ->and($share->allow_downloads)->toBeTrue()
        ->and(Hash::check('secret-password', (string) $share->getRawOriginal('password')))->toBeTrue();
});

test('applies disabled downloads and no password by default', function (): void {
    Livewire::test(CreateMemoryWallShare::class)
        ->fillForm(['name' => 'Simple gallery'])
        ->call('create')
        ->assertHasNoFormErrors();

    $share = MemoryWallShare::query()->where('name', 'Simple gallery')->sole();

    expect($share->wedding_id)->toBe($this->user->team->wedding->getKey())
        ->and($share->allow_downloads)->toBeFalse()
        ->and($share->password)->toBeNull()
        ->and($share->expires_at)->toBeNull();
});

test('rejects an empty name and an expiry in the past', function (): void {
    Livewire::test(CreateMemoryWallShare::class)
        ->fillForm([
            'name' => null,
            'expires_at' => now()->subMinute()->format('Y-m-d H:i:s'),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'expires_at' => 'after',
        ])
        ->assertNotNotified();

    assertDatabaseCount('memory_wall_shares', 0);
});
