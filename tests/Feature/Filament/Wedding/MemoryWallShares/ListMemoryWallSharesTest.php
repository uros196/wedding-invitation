<?php

declare(strict_types=1);

use App\Filament\Wedding\Pages\ManageWedding\MemoryWallSharesManager;
use App\Filament\Wedding\Resources\MemoryWallShares\Pages\ListMemoryWallShares;
use App\Models\MemoryWallShare;
use Livewire\Livewire;

test('lists only share links belonging to the authenticated wedding', function (): void {
    $visibleShare = MemoryWallShare::factory()
        ->for($this->user->team->wedding)
        ->create(['name' => 'Family gallery']);
    $hiddenShare = MemoryWallShare::factory()->create(['name' => 'Another wedding']);

    Livewire::test(ListMemoryWallShares::class)
        ->assertCanSeeTableRecords([$visibleShare])
        ->assertCanNotSeeTableRecords([$hiddenShare]);
});

test('shows only the current wedding links in wedding settings', function (): void {
    $visibleShare = MemoryWallShare::factory()
        ->for($this->user->team->wedding)
        ->create(['name' => 'My internal label']);
    $hiddenShare = MemoryWallShare::factory()->create(['name' => 'Other internal label']);

    Livewire::test(MemoryWallSharesManager::class)
        ->assertCanSeeTableRecords([$visibleShare])
        ->assertCanNotSeeTableRecords([$hiddenShare]);
});

test('shows share status metadata and the canonical public URL', function (): void {
    $share = MemoryWallShare::factory()
        ->for($this->user->team->wedding)
        ->create([
            'name' => 'Public gallery',
            'expires_at' => now()->addDay(),
            'allow_downloads' => true,
        ]);

    Livewire::test(ListMemoryWallShares::class)
        ->assertCanSeeTableRecords([$share])
        ->assertSee(__('Active'))
        ->assertSee(__('Copy Link'))
        ->assertSee(route('memory-wall.share.show', ['share' => $share]), false);
});

test('marks an expired share link in the table', function (): void {
    $share = MemoryWallShare::factory()
        ->for($this->user->team->wedding)
        ->expired()
        ->create();

    Livewire::test(ListMemoryWallShares::class)
        ->assertCanSeeTableRecords([$share])
        ->assertSee(__('Expired'));
});
