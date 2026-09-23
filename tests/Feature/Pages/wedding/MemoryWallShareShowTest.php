<?php

declare(strict_types=1);

use App\Contracts\MemoryWallMediaUrl;
use App\Enums\MediaStatus;
use App\Enums\MemoryWallUploadStatus;
use App\Models\Media;
use App\Models\MemoryWallShare;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function addMemoryWallShareImage(Wedding $wedding, string $name): Media
{
    $media = $wedding
        ->addMedia(UploadedFile::fake()->image($name))
        ->toMediaCollection(Wedding::MEMORY_WALL_COLLECTION);

    $media->forceFill([
        'generated_conversions' => ['preview' => true],
        'status' => MediaStatus::Ready,
    ])->save();

    return $media;
}

test('renders an unlocked share with metadata and one cursor page', function (): void {
    Storage::fake('public');
    config(['memory-wall.share_page_size' => 1]);

    $wedding = Wedding::factory()->memoryWallEnabled()->create();
    addMemoryWallShareImage($wedding, 'first.jpg');
    $secondMedia = addMemoryWallShareImage($wedding, 'second.jpg');
    $share = MemoryWallShare::factory()->for($wedding)->create([
        'name' => 'Reception memories',
    ]);

    $this->get(route('memory-wall.share.show', $share))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memory-wall-share')
            ->where('share.uuid', $share->uuid)
            ->where('share.title', __('wedding.memory_wall.share.public_title'))
            ->missing('share.name')
            ->where('requiresPassword', false)
            ->where('allowDownloads', false)
            ->has('media.data', 1)
            ->where('media.data.0.uuid', $secondMedia->uuid)
            ->missing('media.data.0.download_url')
            ->has('media.next_cursor')
        );
});

test('does not expose media before a password-protected share is unlocked', function (): void {
    $wedding = Wedding::factory()->memoryWallEnabled()->create();
    addMemoryWallShareImage($wedding, 'private.jpg');
    $share = MemoryWallShare::factory()->for($wedding)->passwordProtected('correct-password')->create();

    $this->get(route('memory-wall.share.show', $share))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memory-wall-share')
            ->where('requiresPassword', true)
            ->where('media', null)
            ->missing('share.password')
        );
});

test('includes the existing direct download URL only when downloads are allowed', function (): void {
    Storage::fake('public');
    $wedding = Wedding::factory()->memoryWallEnabled()->create();
    $media = addMemoryWallShareImage($wedding, 'downloadable.jpg');
    $share = MemoryWallShare::factory()->for($wedding)->create([
        'allow_downloads' => true,
    ]);
    $downloadUrl = mock(MemoryWallMediaUrl::class);
    $downloadUrl->shouldReceive('make')
        ->once()
        ->withArgs(fn (Media $actualMedia, string $filename): bool => $actualMedia->is($media)
            && $filename === 'downloadable.jpg')
        ->andReturn('https://storage.example.test/downloadable.jpg');
    $this->app->instance(MemoryWallMediaUrl::class, $downloadUrl);

    $this->get(route('memory-wall.share.show', $share))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('allowDownloads', true)
            ->where('media.data.0.download_url', 'https://storage.example.test/downloadable.jpg')
        );
});

test('returns only ready completed or legacy media from the live share query', function (): void {
    Storage::fake('public');
    $wedding = Wedding::factory()->memoryWallEnabled()->create();
    $legacyMedia = addMemoryWallShareImage($wedding, 'legacy.jpg');
    $completedMedia = addMemoryWallShareImage($wedding, 'completed.jpg');
    $pendingMedia = addMemoryWallShareImage($wedding, 'pending.jpg');
    $failedMedia = addMemoryWallShareImage($wedding, 'failed.jpg');

    MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $completedMedia->id,
        'status' => MemoryWallUploadStatus::Completed,
    ]);
    MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $pendingMedia->id,
        'status' => MemoryWallUploadStatus::Processing,
    ]);
    MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $failedMedia->id,
        'status' => MemoryWallUploadStatus::Failed,
    ]);
    $share = MemoryWallShare::factory()->for($wedding)->create();

    $this->get(route('memory-wall.share.show', $share))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('media.data', 2)
            ->where('media.data.0.uuid', $completedMedia->uuid)
            ->where('media.data.1.uuid', $legacyMedia->uuid)
        );
});

test('never includes media from another wedding in a share gallery', function (): void {
    Storage::fake('public');
    $ownerWedding = Wedding::factory()->memoryWallEnabled()->create();
    $otherWedding = Wedding::factory()->memoryWallEnabled()->create();
    $ownerMedia = addMemoryWallShareImage($ownerWedding, 'owner.jpg');
    addMemoryWallShareImage($otherWedding, 'other.jpg');
    $share = MemoryWallShare::factory()->for($ownerWedding)->create();

    $this->get(route('memory-wall.share.show', $share))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('media.data', 1)
            ->where('media.data.0.uuid', $ownerMedia->uuid)
        );
});

test('hides an expired, disabled, or unknown share', function (): void {
    $wedding = Wedding::factory()->memoryWallEnabled()->create();
    $expiredShare = MemoryWallShare::factory()->for($wedding)->expired()->create();

    $this->get(route('memory-wall.share.show', $expiredShare))->assertNotFound();

    $wedding->update(['has_memory_wall' => false]);
    $activeShare = MemoryWallShare::factory()->for($wedding)->create();

    $this->get(route('memory-wall.share.show', $activeShare))->assertNotFound();
    $this->get(route('memory-wall.share.show', [
        'share' => '00000000-0000-4000-8000-000000000000',
    ]))->assertNotFound();
});
