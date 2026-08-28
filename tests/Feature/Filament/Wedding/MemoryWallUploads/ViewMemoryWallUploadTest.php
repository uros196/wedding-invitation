<?php

declare(strict_types=1);

use App\Enums\MemoryWallUploadStatus;
use App\Filament\Wedding\Resources\MemoryWallUploads\Pages\ViewMemoryWallUpload;
use App\Models\MemoryWallUpload;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('shows the selected upload details', function (): void {
    $upload = MemoryWallUpload::factory()->for($this->user->team->wedding)->create([
        'original_name' => 'ceremony-video.mp4',
        'status' => MemoryWallUploadStatus::Completed,
    ]);

    Livewire::test(ViewMemoryWallUpload::class, ['record' => $upload->id])
        ->assertSee('ceremony-video.mp4')
        ->assertSee(__('wedding.memory_wall.status.completed'));
});

test('shows the generated image conversion on the details page', function (): void {
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

    Livewire::test(ViewMemoryWallUpload::class, ['record' => $upload->id])
        ->assertSee($media->fresh()->getUrl('preview'), false);
});

test('plays a video on the details page', function (): void {
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

    Livewire::test(ViewMemoryWallUpload::class, ['record' => $upload->id])
        ->assertSeeHtmlInOrder([
            '<video',
            'controls',
            '<source',
            $media->fresh()->getUrl(),
        ])
        ->assertSee('video-placeholder.svg', false);
});

test('cannot view an upload belonging to another wedding', function (): void {
    $upload = MemoryWallUpload::factory()->create();

    expect(fn (): mixed => Livewire::test(ViewMemoryWallUpload::class, ['record' => $upload->id]))
        ->toThrow(ModelNotFoundException::class);
});
