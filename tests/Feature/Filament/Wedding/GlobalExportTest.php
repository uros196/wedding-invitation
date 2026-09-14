<?php

declare(strict_types=1);

use App\Livewire\GlobalExport;
use Livewire\Livewire;

test('shows the memory wall archive export when the memory wall is enabled', function (): void {
    Livewire::test(GlobalExport::class)
        ->assertSee(__('wedding.memory_wall.download.all'));
});

test('hides the memory wall archive export when the memory wall is disabled', function (): void {
    $this->user->team->wedding->update(['has_memory_wall' => false]);

    Livewire::test(GlobalExport::class)
        ->assertDontSee(__('wedding.memory_wall.download.all'));
});
