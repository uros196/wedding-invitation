<?php

declare(strict_types=1);

use App\Filament\Wedding\Resources\MemoryWallShares\Pages\EditMemoryWallShare;
use App\Models\MemoryWallShare;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\assertModelMissing;

test('edits a share link while preserving its password when the password field is blank', function (): void {
    $share = MemoryWallShare::factory()
        ->for($this->user->team->wedding)
        ->passwordProtected('old-password')
        ->create(['name' => 'Before edit']);

    Livewire::test(EditMemoryWallShare::class, ['record' => $share->getRouteKey()])
        ->assertFormFieldVisible('clear_password')
        ->fillForm([
            'name' => 'After edit',
            'password' => null,
            'allow_downloads' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $share->refresh();

    expect($share->name)->toBe('After edit')
        ->and($share->allow_downloads)->toBeTrue()
        ->and(Hash::check('old-password', (string) $share->getRawOriginal('password')))->toBeTrue();
});

test('can change and explicitly clear a share password', function (): void {
    $share = MemoryWallShare::factory()
        ->for($this->user->team->wedding)
        ->passwordProtected('old-password')
        ->create();

    Livewire::test(EditMemoryWallShare::class, ['record' => $share->getRouteKey()])
        ->fillForm(['password' => 'new-password'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('new-password', (string) $share->refresh()->getRawOriginal('password')))->toBeTrue();

    Livewire::test(EditMemoryWallShare::class, ['record' => $share->getRouteKey()])
        ->fillForm([
            'password' => null,
            'clear_password' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($share->refresh()->password)->toBeNull();
});

test('deletes a share link', function (): void {
    $share = MemoryWallShare::factory()->for($this->user->team->wedding)->create();

    Livewire::test(EditMemoryWallShare::class, ['record' => $share->getRouteKey()])
        ->callAction(DeleteAction::class)
        ->assertNotified();

    assertModelMissing($share);
});

test('does not load a share link belonging to another wedding', function (): void {
    $hiddenShare = MemoryWallShare::factory()->create();

    expect(fn (): mixed => Livewire::test(EditMemoryWallShare::class, [
        'record' => $hiddenShare->getRouteKey(),
    ]))->toThrow(ModelNotFoundException::class);
});
