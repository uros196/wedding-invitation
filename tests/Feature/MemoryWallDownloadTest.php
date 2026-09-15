<?php

declare(strict_types=1);

use App\Contracts\MemoryWallArchiveSink;
use App\Contracts\MemoryWallArchiveStorage;
use App\Enums\MemoryWallDownloadStatus;
use App\Enums\MemoryWallUploadStatus;
use App\Events\MemoryWallDownloadUpdated;
use App\Jobs\CleanupExpiredMemoryWallDownloadsJob;
use App\Jobs\PrepareMemoryWallDownloadJob;
use App\Livewire\MemoryWallDownloadManager;
use App\Models\MemoryWallDownload;
use App\Models\MemoryWallDownloadItem;
use App\Models\MemoryWallUpload;
use App\Models\User;
use App\Services\MemoryWall\Archive\StreamingZipWriter;
use App\Services\MemoryWall\CreateMemoryWallDownload;
use App\Services\MemoryWall\PrepareMemoryWallArchive;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('creates a snapshot and queues one archive preparation job', function (): void {
    Storage::fake('public');
    config([
        'memory-wall.media_disk' => 'public',
        'memory-wall.archive_disk' => 'public',
    ]);
    $user = User::factory()->weddingTeamMember()->create();
    $wedding = $user->team->wedding;
    $media = $wedding->addMedia(UploadedFile::fake()->create('memory.mp4', 32, 'video/mp4'))
        ->toMediaCollection('MemoryWall', 'public');
    $upload = MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $media->id,
        'original_name' => 'guest-video.mp4',
        'status' => MemoryWallUploadStatus::Completed,
    ]);
    Queue::fake();

    $download = app(CreateMemoryWallDownload::class)->handle($wedding, $user);

    $this->assertModelExists($download);
    expect($download->status)->toBe(MemoryWallDownloadStatus::Queued)
        ->and($download->total_files)->toBe(1)
        ->and($download->total_bytes)->toBe($media->size)
        ->and($download->items()->first()->memory_wall_upload_id)->toBe($upload->id);
    Queue::assertPushed(PrepareMemoryWallDownloadJob::class, function (PrepareMemoryWallDownloadJob $job) use ($download): bool {
        return $job->download->is($download);
    });
});

test('provides download manager styles for archive statuses', function (): void {
    expect(MemoryWallDownloadStatus::Ready->getDownloadManagerStyle())
        ->toMatchArray([
            'icon' => 'heroicon-o-check-circle',
            'iconClass' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400',
            'badgeClass' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
        ])
        ->and(MemoryWallDownloadStatus::Failed->getDownloadManagerStyle()['icon'])
        ->toBe('heroicon-o-exclamation-triangle')
        ->and(MemoryWallDownloadStatus::Processing->getDownloadManagerStyle()['icon'])
        ->toBe('heroicon-o-arrow-path');
});

test('writes a readable zip64 archive without buffering source files', function (): void {
    $bytes = (object) ['value' => ''];
    $sink = new class($bytes) implements MemoryWallArchiveSink
    {
        public function __construct(private readonly object $bytes) {}

        public function write(string $contents): void
        {
            $this->bytes->value .= $contents;
        }

        public function finish(): array
        {
            return [];
        }
    };
    $writer = new StreamingZipWriter($sink);
    $writer->addFile(
        'photo.txt',
        static fn () => fopen('data://text/plain,hello', 'rb'),
        5,
    );
    $writer->finish();

    $path = tempnam(sys_get_temp_dir(), 'memory-wall-zip-');
    file_put_contents($path, $bytes->value);
    $archive = new ZipArchive;

    $operatingSystem = 0;
    $externalAttributes = 0;

    expect($archive->open($path))->toBeTrue()
        ->and($archive->getExternalAttributesIndex(0, $operatingSystem, $externalAttributes))->toBeTrue()
        ->and($externalAttributes & 0x10)->toBe(0)
        ->and($archive->getFromName('photo.txt'))->toBe('hello');

    $archive->close();
    unlink($path);
});

test('prepares an archive through multipart storage and reports completion', function (): void {
    Storage::fake('public');
    config('memory-wall.media_disk', 'public');
    $user = User::factory()->weddingTeamMember()->create();
    $wedding = $user->team->wedding;
    $media = $wedding->addMedia(UploadedFile::fake()->createWithContent('memory.txt', 'hello'))
        ->toMediaCollection('MemoryWall', 'public');
    $download = MemoryWallDownload::create([
        'wedding_id' => $wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 'public',
        'archive_path' => 'memory-wall/archive.zip',
        'archive_name' => 'archive.zip',
        'status' => MemoryWallDownloadStatus::Queued,
        'total_bytes' => $media->size,
        'total_files' => 1,
    ]);
    MemoryWallDownloadItem::create([
        'memory_wall_download_id' => $download->id,
        'disk' => 'public',
        'path' => $media->getPathRelativeToRoot(),
        'original_name' => 'memory.txt',
        'archive_name' => 'memory.txt',
        'size' => $media->size,
        'mime_type' => 'text/plain',
    ]);
    $storage = new class implements MemoryWallArchiveStorage
    {
        public string $contents = '';

        public function createMultipartUpload(string $path, string $filename): string
        {
            return 'upload-id';
        }

        public function uploadPart(string $path, string $uploadId, int $partNumber, string $contents): string
        {
            $this->contents .= $contents;

            return "etag-{$partNumber}";
        }

        public function completeMultipartUpload(string $path, string $uploadId, array $parts): void {}

        public function abortMultipartUpload(string $path, string $uploadId): void {}

        public function temporaryUrl(string $path, DateTimeInterface $expiration, string $filename): string
        {
            return 'https://example.test/archive.zip';
        }

        public function deleteObject(string $path): void {}
    };
    $this->app->instance(MemoryWallArchiveStorage::class, $storage);
    Event::fake();

    app(PrepareMemoryWallArchive::class)->handle($download);

    $archivePath = tempnam(sys_get_temp_dir(), 'memory-wall-prepared-');
    file_put_contents($archivePath, $storage->contents);
    $archive = new ZipArchive;

    expect($archive->open($archivePath))->toBeTrue()
        ->and($archive->getFromName('memory.txt'))->toBe('hello');

    $archive->close();
    unlink($archivePath);

    expect($download->fresh()->status)->toBe(MemoryWallDownloadStatus::Ready)
        ->and($download->fresh()->processed_bytes)->toBe($media->size)
        ->and($download->fresh()->processed_files)->toBe(1)
        ->and($storage->contents)->toContain('memory.txt');
    Event::assertDispatched(MemoryWallDownloadUpdated::class);
});

test('aborts multipart preparation when cancellation arrives during storage work', function (): void {
    Storage::fake('public');
    config('memory-wall.media_disk', 'public');
    $user = User::factory()->weddingTeamMember()->create();
    $wedding = $user->team->wedding;
    $media = $wedding->addMedia(UploadedFile::fake()->createWithContent('memory.txt', 'hello'))
        ->toMediaCollection('MemoryWall', 'public');
    $download = MemoryWallDownload::create([
        'wedding_id' => $wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 'public',
        'archive_path' => 'memory-wall/cancelled-during-work.zip',
        'archive_name' => 'cancelled-during-work.zip',
        'status' => MemoryWallDownloadStatus::Queued,
        'total_bytes' => $media->size,
        'total_files' => 1,
    ]);
    MemoryWallDownloadItem::create([
        'memory_wall_download_id' => $download->id,
        'disk' => 'public',
        'path' => $media->getPathRelativeToRoot(),
        'original_name' => 'memory.txt',
        'archive_name' => 'memory.txt',
        'size' => $media->size,
        'mime_type' => 'text/plain',
    ]);
    $storage = new class($download) implements MemoryWallArchiveStorage
    {
        public int $abortCalls = 0;

        public function __construct(private readonly MemoryWallDownload $download) {}

        public function createMultipartUpload(string $path, string $filename): string
        {
            return 'upload-id';
        }

        public function uploadPart(string $path, string $uploadId, int $partNumber, string $contents): string
        {
            $this->download->forceFill([
                'status' => MemoryWallDownloadStatus::Cancelling,
                'cancellation_requested_at' => now(),
            ])->saveQuietly();

            return "etag-{$partNumber}";
        }

        public function completeMultipartUpload(string $path, string $uploadId, array $parts): void
        {
            throw new RuntimeException('The cancelled archive must not be completed.');
        }

        public function abortMultipartUpload(string $path, string $uploadId): void
        {
            $this->abortCalls++;
        }

        public function temporaryUrl(string $path, DateTimeInterface $expiration, string $filename): string
        {
            return 'https://example.test/archive.zip';
        }

        public function deleteObject(string $path): void {}
    };
    $this->app->instance(MemoryWallArchiveStorage::class, $storage);
    Event::fake();

    app(PrepareMemoryWallArchive::class)->handle($download);

    expect($download->fresh()->status)->toBe(MemoryWallDownloadStatus::Cancelled)
        ->and($storage->abortCalls)->toBe(1);
});

test('rejects archive download links after the temporary url expires', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');
    $wedding = $user->team->wedding;
    $download = MemoryWallDownload::create([
        'wedding_id' => $wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/archive.zip',
        'archive_name' => 'archive.zip',
        'status' => MemoryWallDownloadStatus::Ready,
        'total_bytes' => 1,
        'processed_bytes' => 1,
        'total_files' => 1,
        'processed_files' => 1,
        'expires_at' => now()->subMinute(),
    ]);

    $this->get(route('memory-wall.downloads.file', [
        'wedding' => $wedding,
        'memoryWallDownload' => $download,
    ]))->assertGone();
});

test('redirects ready archives to a temporary storage url', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');
    $wedding = $user->team->wedding;
    $download = MemoryWallDownload::create([
        'wedding_id' => $wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/archive.zip',
        'archive_name' => 'archive.zip',
        'status' => MemoryWallDownloadStatus::Ready,
        'expires_at' => now()->addHour(),
    ]);
    $storage = mock(MemoryWallArchiveStorage::class);
    $storage->shouldReceive('temporaryUrl')
        ->once()
        ->with('memory-wall/archive.zip', Mockery::type(DateTimeInterface::class), 'archive.zip')
        ->andReturn('https://s3.example.test/archive.zip');
    $this->app->instance(MemoryWallArchiveStorage::class, $storage);

    $this->get(route('memory-wall.downloads.file', [
        'wedding' => $wedding,
        'memoryWallDownload' => $download,
    ]))->assertRedirect('https://s3.example.test/archive.zip');
});

test('cleans up expired archive objects', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $download = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/expired.zip',
        'archive_name' => 'expired.zip',
        'status' => MemoryWallDownloadStatus::Ready,
        'expires_at' => now()->subMinute(),
    ]);
    $storage = mock(MemoryWallArchiveStorage::class);
    $storage->shouldReceive('deleteObject')->once()->with('memory-wall/expired.zip');

    (new CleanupExpiredMemoryWallDownloadsJob)->handle($storage);

    expect($download->fresh()->status)->toBe(MemoryWallDownloadStatus::Expired)
        ->and($download->fresh()->archive_path)->toBeNull();
});

test('cleans up stale cancellation requests after the configured retention period', function (): void {
    config(['memory-wall.cancellation_cleanup_after_days' => 3]);
    $user = User::factory()->weddingTeamMember()->create();
    $staleDownload = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/stale-cancelling.zip',
        'archive_name' => 'stale-cancelling.zip',
        'status' => MemoryWallDownloadStatus::Cancelling,
        'cancellation_requested_at' => now()->subDays(3),
    ]);
    $recentDownload = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/recent-cancelling.zip',
        'archive_name' => 'recent-cancelling.zip',
        'status' => MemoryWallDownloadStatus::Cancelling,
        'cancellation_requested_at' => now()->subDays(2),
    ]);
    $storage = mock(MemoryWallArchiveStorage::class);
    $storage->shouldReceive('deleteObject')->once()->with('memory-wall/stale-cancelling.zip');

    (new CleanupExpiredMemoryWallDownloadsJob)->handle($storage);

    expect($staleDownload->fresh()->status)->toBe(MemoryWallDownloadStatus::Cancelled)
        ->and($staleDownload->fresh()->archive_path)->toBeNull()
        ->and($recentDownload->fresh()->status)->toBe(MemoryWallDownloadStatus::Cancelling)
        ->and($recentDownload->fresh()->archive_path)->toBe('memory-wall/recent-cancelling.zip');
});

test('requests cancellation for a processing archive from the download manager', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');
    $download = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/processing.zip',
        'archive_name' => 'processing.zip',
        'status' => MemoryWallDownloadStatus::Processing,
        'total_bytes' => 100,
    ]);

    Livewire::test(MemoryWallDownloadManager::class)
        ->call('removeDownload', $download->uuid);

    expect($download->fresh()->status)->toBe(MemoryWallDownloadStatus::Cancelling);
});

test('deletes a ready archive from the download manager', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');
    $download = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/ready.zip',
        'archive_name' => 'ready.zip',
        'status' => MemoryWallDownloadStatus::Ready,
        'expires_at' => now()->addHour(),
    ]);
    $storage = mock(MemoryWallArchiveStorage::class);
    $storage->shouldReceive('deleteObject')->once()->with('memory-wall/ready.zip');
    $this->app->instance(MemoryWallArchiveStorage::class, $storage);

    Livewire::test(MemoryWallDownloadManager::class)
        ->call('removeDownload', $download->uuid);

    expect($download->fresh()->status)->toBe(MemoryWallDownloadStatus::Cancelled)
        ->and($download->fresh()->archive_path)->toBeNull();
});

test('cleans up cancelled archive objects', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $download = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/cancelled.zip',
        'archive_name' => 'cancelled.zip',
        'status' => MemoryWallDownloadStatus::Cancelled,
    ]);
    $storage = mock(MemoryWallArchiveStorage::class);
    $storage->shouldReceive('deleteObject')->once()->with('memory-wall/cancelled.zip');

    (new CleanupExpiredMemoryWallDownloadsJob)->handle($storage);
    (new CleanupExpiredMemoryWallDownloadsJob)->handle($storage);

    expect($download->fresh()->archive_path)->toBeNull();
});

test('confirms cancellation when the worker starts after cancellation was requested', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $download = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/waiting.zip',
        'archive_name' => 'waiting.zip',
        'status' => MemoryWallDownloadStatus::Cancelling,
        'cancellation_requested_at' => now(),
    ]);
    Event::fake();

    app(PrepareMemoryWallArchive::class)->handle($download);

    expect($download->fresh()->status)->toBe(MemoryWallDownloadStatus::Cancelled);
});

test('renders progress for the current wedding downloads', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');
    $download = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/archive.zip',
        'archive_name' => 'archive.zip',
        'status' => MemoryWallDownloadStatus::Processing,
        'total_bytes' => 100,
        'processed_bytes' => 50,
        'total_files' => 2,
        'processed_files' => 1,
    ]);

    $component = Livewire::test(MemoryWallDownloadManager::class)
        ->assertSee($download->archive_name)
        ->assertSee('50%')
        ->assertSee(__('wedding.memory_wall.download.title'))
        ->assertSeeHtml('animate-ping')
        ->assertSee('fixed')
        ->assertDontSeeHtml('wire:poll')
        ->assertSeeHtml('x-data="{')
        ->assertSeeHtml('collapsed: false')
        ->assertSeeHtml("storageKey: 'memory-wall-download-manager-collapsed'")
        ->assertSeeHtml("this.collapsed = localStorage.getItem(this.storageKey) === 'true';")
        ->assertSeeHtml("localStorage.setItem(this.storageKey, this.collapsed ? 'true' : 'false');")
        ->assertSeeHtml('x-on:click="toggle"')
        ->assertSeeHtml("wire:click=\"mountAction('removeDownload', { uuid: '{$download->uuid}' })\"")
        ->assertDontSeeHtml('wire:confirm=')
        ->assertSeeHtml('wire:partial="action-modals"')
        ->assertSeeHtml('x-show="! collapsed"')
        ->assertSeeHtml('role="progressbar"');

    $download->update([
        'status' => MemoryWallDownloadStatus::Ready,
        'expires_at' => now()->addHour(),
    ]);

    $component->call('handleDownloadUpdated')
        ->assertDispatched('memory-wall-download-ready', uuid: $download->uuid)
        ->assertSee(__('wedding.memory_wall.download.ready_title'))
        ->assertDontSeeHtml('animate-ping');
});

test('tracks automatic and manual archive download requests in the manager', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');
    $download = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/archive.zip',
        'archive_name' => 'archive.zip',
        'status' => MemoryWallDownloadStatus::Ready,
        'expires_at' => now()->addHour(),
    ]);

    Livewire::test(MemoryWallDownloadManager::class)
        ->assertSeeHtml('x-on:memory-wall-download-ready.window="markDownloadStarted($event.detail.uuid)"')
        ->assertSeeHtml("x-on:click=\"markDownloadStarted('{$download->uuid}')\"")
        ->assertSeeHtml('downloadResetDelay: 2500')
        ->assertSeeHtml('startedDownloadTimers: {},')
        ->assertSeeHtml('clearTimeout(this.startedDownloadTimers[uuid]);')
        ->assertSeeHtml('window.setTimeout')
        ->assertSeeHtml('delete this.startedDownloads[uuid];')
        ->assertSeeHtml('delete this.startedDownloadTimers[uuid];');
});

test('keeps a failed archive message collapsed until expanded', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');
    $error = str_repeat('The archive source could not be read. ', 8);
    MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => null,
        'archive_name' => 'failed-archive.zip',
        'status' => MemoryWallDownloadStatus::Failed,
        'error_message' => $error,
    ]);

    $html = Livewire::test(MemoryWallDownloadManager::class)->html();

    expect($html)
        ->toContain('<details')
        ->toContain('<summary')
        ->not->toContain('<details open');
});

test('uses a Filament confirmation modal before removing a download', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');
    $download = MemoryWallDownload::create([
        'wedding_id' => $user->team->wedding->id,
        'user_id' => $user->id,
        'uuid' => MemoryWallDownload::newUuid(),
        'disk' => 's3',
        'archive_path' => 'memory-wall/processing.zip',
        'archive_name' => 'processing.zip',
        'status' => MemoryWallDownloadStatus::Processing,
        'total_bytes' => 100,
    ]);

    Livewire::test(MemoryWallDownloadManager::class)
        ->call('mountAction', 'removeDownload', ['uuid' => $download->uuid])
        ->assertSet('mountedActions.0.name', 'removeDownload')
        ->assertSet('mountedActions.0.arguments.uuid', $download->uuid)
        ->call('callMountedAction');

    expect($download->fresh()->status)->toBe(MemoryWallDownloadStatus::Cancelling);
});

test('keeps the download manager mounted when there are no downloads', function (): void {
    $user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($user, 'wedding');

    Livewire::test(MemoryWallDownloadManager::class)
        ->assertDontSeeHtml('wire:poll')
        ->assertDontSee('Preparing memory wall download');
});

test('registers one echo listener per wedding channel', function (): void {
    $user = User::factory()->weddingTeamMember()->create();

    $html = view('filament.wedding.echo-register', ['user' => $user])->render();

    expect($html)
        ->toContain('const listenerKey = `wedding-echo-listener:${channelName}`;')
        ->toContain('if (window[listenerKey]) {')
        ->toContain("Livewire.dispatch('memory-wall-download-updated')");
});
