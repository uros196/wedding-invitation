<?php

declare(strict_types=1);

use App\Filament\Wedding\Pages\ManageWedding\MemoryWallSharesManager;
use App\Filament\Wedding\Resources\MemoryWallShares\Pages\CreateMemoryWallShare;
use App\Models\MemoryWallShare;
use Filament\Actions\Testing\TestAction;
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

test('creates a share link without an internal name', function (): void {
    Livewire::test(CreateMemoryWallShare::class)
        ->fillForm(['name' => null])
        ->call('create')
        ->assertHasNoFormErrors();

    $share = MemoryWallShare::query()->sole();

    expect($share->name)->toBeNull()
        ->and($share->wedding_id)->toBe($this->user->team->wedding->getKey());
});

test('creates a settings link for the current wedding and ignores forged ownership', function (): void {
    $wedding = $this->user->team->wedding;
    $wedding->update(['has_memory_wall' => true]);
    $otherWedding = MemoryWallShare::factory()->create()->wedding;

    Livewire::test(MemoryWallSharesManager::class)
        ->callAction(TestAction::make('createShare')->table(), [
            'name' => null,
            'wedding_id' => $otherWedding->getKey(),
        ])
        ->assertHasNoFormErrors()
        ->assertNotified();

    $share = $wedding->memoryWallShares()->sole();

    expect($share->name)->toBeNull()
        ->and($share->allow_downloads)->toBeFalse();
});

test('rejects an expiry in the past while allowing an empty name', function (): void {
    Livewire::test(CreateMemoryWallShare::class)
        ->fillForm([
            'name' => null,
            'expires_at' => now()->subMinute()->format('Y-m-d H:i:s'),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'expires_at' => 'after',
        ])
        ->assertNotNotified();

    assertDatabaseCount('memory_wall_shares', 0);
});
