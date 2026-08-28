<?php

declare(strict_types=1);

use App\Enums\MemoryWallUploadStatus;
use App\Filament\Wedding\Resources\MemoryWallUploads\Pages\ListMemoryWallUploads;
use App\Models\MemoryWallUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('lists only memory wall uploads belonging to the authenticated wedding', function (): void {
    $visibleUpload = MemoryWallUpload::factory()->for($this->user->team->wedding)->create([
        'original_name' => 'visible-photo.jpg',
        'status' => MemoryWallUploadStatus::Completed,
    ]);
    $hiddenUpload = MemoryWallUpload::factory()->create([
        'original_name' => 'hidden-photo.jpg',
        'status' => MemoryWallUploadStatus::Completed,
    ]);

    Livewire::test(ListMemoryWallUploads::class)
        ->assertCanSeeTableRecords([$visibleUpload])
        ->assertCanNotSeeTableRecords([$hiddenUpload]);
});

test('shows the generated image conversion in the table', function (): void {
    Storage::fake('public');
    $wedding = $this->user->team->wedding;
    $media = $wedding->addMedia(UploadedFile::fake()->image('memory.jpg'))
        ->toMediaCollection('MemoryWall', 'public');
    $media->forceFill(['generated_conversions' => ['preview' => true]])->save();

    $upload = MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $media->id,
        'mime_type' => 'image/jpeg',
        'original_name' => 'memory.jpg',
        'status' => MemoryWallUploadStatus::Completed,
    ]);

    Livewire::test(ListMemoryWallUploads::class)
        ->assertCanSeeTableRecords([$upload])
        ->assertSee($media->fresh()->getUrl('preview'), false);
});

test('shows a video placeholder in the table', function (): void {
    Storage::fake('public');
    $wedding = $this->user->team->wedding;
    $media = $wedding->addMedia(UploadedFile::fake()->create('memory.mp4', 32, 'video/mp4'))
        ->toMediaCollection('MemoryWall', 'public');

    $upload = MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $media->id,
        'mime_type' => 'video/mp4',
        'original_name' => 'memory.mp4',
        'status' => MemoryWallUploadStatus::Completed,
    ]);

    Livewire::test(ListMemoryWallUploads::class)
        ->assertCanSeeTableRecords([$upload])
        ->assertSee('video-placeholder.svg', false);
});
