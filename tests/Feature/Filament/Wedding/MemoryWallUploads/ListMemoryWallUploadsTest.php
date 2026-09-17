<?php

declare(strict_types=1);

use App\Contracts\MemoryWallMediaUrl;
use App\Enums\MemoryWallUploadStatus;
use App\Filament\Wedding\Resources\MemoryWallUploads\Pages\ListMemoryWallUploads;
use App\Jobs\PrepareMemoryWallDownloadJob;
use App\Models\MemoryWallDownload;
use App\Models\MemoryWallUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
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
        ->assertSee($media->fresh()->getUrl('preview'), false)
        ->assertSee($upload->mime_type)
        ->assertSee(Number::fileSize($upload->expected_size));
});

test('shows the newest memory wall uploads first', function (): void {
    $wedding = $this->user->team->wedding;
    $olderUpload = MemoryWallUpload::factory()->for($wedding)->create([
        'original_name' => 'older-memory.jpg',
        'created_at' => now()->subDay(),
    ]);
    $newerUpload = MemoryWallUpload::factory()->for($wedding)->create([
        'original_name' => 'newer-memory.jpg',
        'created_at' => now(),
    ]);

    Livewire::test(ListMemoryWallUploads::class)
        ->assertSeeHtmlInOrder([
            $newerUpload->original_name,
            $olderUpload->original_name,
        ]);
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

test('filters memory wall uploads by media type', function (): void {
    $wedding = $this->user->team->wedding;
    $image = MemoryWallUpload::factory()->for($wedding)->create([
        'mime_type' => 'image/jpeg',
        'original_name' => 'memory.jpg',
    ]);
    $video = MemoryWallUpload::factory()->for($wedding)->create([
        'mime_type' => 'video/mp4',
        'original_name' => 'memory.mp4',
    ]);

    Livewire::test(ListMemoryWallUploads::class)
        ->filterTable('media_type', 'image')
        ->assertCanSeeTableRecords([$image])
        ->assertCanNotSeeTableRecords([$video]);
});

test('filters memory wall uploads by status', function (): void {
    $wedding = $this->user->team->wedding;
    $completed = MemoryWallUpload::factory()->for($wedding)->create([
        'status' => MemoryWallUploadStatus::Completed,
        'original_name' => 'completed.jpg',
    ]);
    $failed = MemoryWallUpload::factory()->for($wedding)->create([
        'status' => MemoryWallUploadStatus::Failed,
        'original_name' => 'failed.jpg',
    ]);

    Livewire::test(ListMemoryWallUploads::class)
        ->filterTable('status', MemoryWallUploadStatus::Completed->value)
        ->assertCanSeeTableRecords([$completed])
        ->assertCanNotSeeTableRecords([$failed]);
});

test('filters memory wall uploads by size range in megabytes', function (): void {
    $wedding = $this->user->team->wedding;
    $small = MemoryWallUpload::factory()->for($wedding)->create([
        'expected_size' => 1 * 1024 * 1024,
        'original_name' => 'small.jpg',
    ]);
    $matching = MemoryWallUpload::factory()->for($wedding)->create([
        'expected_size' => 3 * 1024 * 1024,
        'original_name' => 'matching.jpg',
    ]);
    $large = MemoryWallUpload::factory()->for($wedding)->create([
        'expected_size' => 5 * 1024 * 1024,
        'original_name' => 'large.jpg',
    ]);

    Livewire::test(ListMemoryWallUploads::class)
        ->filterTable('size', ['from' => 2, 'to' => 4])
        ->assertCanSeeTableRecords([$matching])
        ->assertCanNotSeeTableRecords([$small, $large]);
});

test('creates an archive snapshot only for selected memory wall uploads', function (): void {
    Storage::fake('public');
    config([
        'memory-wall.media_disk' => 'public',
        'memory-wall.archive_disk' => 'public',
    ]);
    $wedding = $this->user->team->wedding;
    $selectedMedia = $wedding->addMedia(UploadedFile::fake()->image('selected.jpg'))
        ->toMediaCollection('MemoryWall', 'public');
    $unselectedMedia = $wedding->addMedia(UploadedFile::fake()->image('unselected.jpg'))
        ->toMediaCollection('MemoryWall', 'public');
    $selectedUpload = MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $selectedMedia->id,
        'original_name' => 'selected-memory.jpg',
    ]);
    $unselectedUpload = MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $unselectedMedia->id,
        'original_name' => 'unselected-memory.jpg',
    ]);
    Queue::fake();

    Livewire::test(ListMemoryWallUploads::class)
        ->callTableBulkAction('downloadSelected', [$selectedUpload])
        ->assertNotified();

    Queue::assertPushed(PrepareMemoryWallDownloadJob::class);

    $download = MemoryWallDownload::query()->firstOrFail();

    expect($download->items()->pluck('memory_wall_upload_id')->all())
        ->toBe([$selectedUpload->id])
        ->and($download->items()->pluck('memory_wall_upload_id')->all())
        ->not->toContain($unselectedUpload->id);
});

test('redirects to a direct original media download with its original name', function (): void {
    Storage::fake('public');
    $wedding = $this->user->team->wedding;
    $file = UploadedFile::fake()->image('stored-file.jpg');
    $media = $wedding->addMedia($file)
        ->usingFileName('stored-file.jpg')
        ->toMediaCollection('MemoryWall', 'public');
    $upload = MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $media->id,
        'mime_type' => 'image/jpeg',
        'original_name' => 'original-memory.jpg',
        'status' => MemoryWallUploadStatus::Completed,
    ]);

    $downloadUrl = mock(MemoryWallMediaUrl::class);
    $downloadUrl->shouldReceive('make')
        ->once()
        ->withArgs(fn ($actualMedia, string $filename): bool => $actualMedia->is($media) && $filename === 'original-memory.jpg')
        ->andReturn('https://s3.example.test/original-memory.jpg');
    $this->app->instance(MemoryWallMediaUrl::class, $downloadUrl);

    $this->get(route('memory-wall.download.single', [
        'wedding' => $wedding,
        'memoryWallUpload' => $upload,
    ]))
        ->assertRedirect('https://s3.example.test/original-memory.jpg');
});
