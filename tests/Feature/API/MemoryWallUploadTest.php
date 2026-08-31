<?php

declare(strict_types=1);

use App\Contracts\MemoryWallMultipartStorage;
use App\DTOs\MemoryWallUploadInitializeData;
use App\Enums\MemoryWallUploadStatus;
use App\Events\MemoryWallUploadProcessed;
use App\Http\Requests\MemoryWall\UploadRequest;
use App\Jobs\CompleteMemoryWallUploadJob;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\CompleteMemoryWallUpload;
use App\Services\MemoryWall\MemoryWallUploadService;
use App\Services\MemoryWall\S3MultipartUploadStorage;
use App\Services\MemoryWall\Upload\Cleanup;
use Aws\S3\S3Client;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

function openMemoryWallWedding(): Wedding
{
    return Wedding::factory()->create([
        'wedding_date' => now()->subDay(),
        'memory_wall_open_until' => now()->addDay(),
    ]);
}

test('converts validated initialization data to a DTO', function (): void {
    $request = UploadRequest::create('/', 'POST');
    $request->setValidator(validator([
        'client_upload_id' => '0f7b3db7-4e4c-4b9c-9f42-e4c05bd9349d',
        'upload_token' => str_repeat('a', 64),
        'file_name' => 'phone-video.mp4',
        'size' => 1024,
        'mime_type' => 'video/mp4',
    ], [
        'client_upload_id' => ['required', 'uuid'],
        'upload_token' => ['required', 'string'],
        'file_name' => ['required', 'string'],
        'size' => ['required', 'integer'],
        'mime_type' => ['required', 'string'],
    ]));

    $data = $request->toDto();

    expect($data)
        ->toBeInstanceOf(MemoryWallUploadInitializeData::class)
        ->clientUploadId->toBe('0f7b3db7-4e4c-4b9c-9f42-e4c05bd9349d')
        ->uploadToken->toBe(str_repeat('a', 64))
        ->originalName->toBe('phone-video.mp4')
        ->size->toBe(1024)
        ->mimeType->toBe('video/mp4');
});

test('stores memory wall upload state without creating media during initialization', function (): void {
    $wedding = openMemoryWallWedding();
    $storage = mock(MemoryWallMultipartStorage::class);
    $storage->shouldReceive('createMultipartUpload')
        ->once()
        ->andReturn('multipart-upload-id');
    $this->app->instance(MemoryWallMultipartStorage::class, $storage);

    $this->postJson(route('memory-wall.upload.initialize', $wedding), [
        'client_upload_id' => '4c9d89d7-9a6a-4d94-86cb-10a91f65b9b4',
        'upload_token' => str_repeat('a', 64),
        'file_name' => 'phone-photo.jpg',
        'size' => 1024,
        'mime_type' => 'image/jpeg',
    ])->assertCreated();

    $upload = MemoryWallUpload::query()
        ->where('client_upload_id', '4c9d89d7-9a6a-4d94-86cb-10a91f65b9b4')
        ->firstOrFail();

    expect($upload->media_id)->toBeNull()
        ->and($upload->media)->toBeNull()
        ->and($wedding->media()->where('collection_name', 'MemoryWall')->exists())->toBeFalse();
});

test('uses the S3 client from the configured memory wall media disk', function (): void {
    $originalMediaDisk = config('memory-wall.media_disk');

    try {
        config(['memory-wall.media_disk' => 'memory-wall-media']);

        $client = mock(S3Client::class);
        $client->shouldReceive('createMultipartUpload')
            ->once()
            ->with([
                'Bucket' => 'memory-wall-bucket',
                'Key' => 'MemoryWall/example.jpg',
                'ContentType' => 'image/jpeg',
            ])
            ->andReturn(['UploadId' => 'multipart-upload-id']);

        $disk = mock(AwsS3V3Adapter::class);
        $disk->shouldReceive('getClient')->once()->andReturn($client);
        $disk->shouldReceive('getConfig')->once()->andReturn([
            'bucket' => 'memory-wall-bucket',
        ]);
        Storage::shouldReceive('disk')
            ->once()
            ->with('memory-wall-media')
            ->andReturn($disk);

        $storage = new S3MultipartUploadStorage;

        expect($storage->createMultipartUpload('MemoryWall/example.jpg', 'image/jpeg'))
            ->toBe('multipart-upload-id');
    } finally {
        config(['memory-wall.media_disk' => $originalMediaDisk]);
    }
});

test('initializes a one gigabyte multipart upload', function (): void {
    $wedding = openMemoryWallWedding();
    $storage = mock(MemoryWallMultipartStorage::class);
    $storage->shouldReceive('createMultipartUpload')
        ->once()
        ->andReturn('multipart-upload-id');
    $this->app->instance(MemoryWallMultipartStorage::class, $storage);

    $response = $this->postJson(route('memory-wall.upload.initialize', $wedding), [
        'client_upload_id' => '0f7b3db7-4e4c-4b9c-9f42-e4c05bd9349d',
        'upload_token' => str_repeat('a', 64),
        'file_name' => 'phone-video.mp4',
        'size' => config('memory-wall.max_file_size'),
        'mime_type' => 'video/mp4',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.total_parts', 128)
        ->assertJsonPath('data.part_size', 8 * 1024 * 1024)
        ->assertJsonStructure([
            'data' => ['uuid', 'upload_token', 'part_size', 'total_parts'],
        ]);

    $upload = MemoryWallUpload::query()
        ->where('client_upload_id', '0f7b3db7-4e4c-4b9c-9f42-e4c05bd9349d')
        ->firstOrFail();

    expect($upload->status)->toBe(MemoryWallUploadStatus::Uploading)
        ->and($upload->wedding_id)->toBe($wedding->id);
});

test('rejects files above the one gigabyte limit before creating an upload', function (): void {
    $wedding = openMemoryWallWedding();
    $storage = mock(MemoryWallMultipartStorage::class);
    $storage->shouldNotReceive('createMultipartUpload');
    $this->app->instance(MemoryWallMultipartStorage::class, $storage);

    $this->postJson(route('memory-wall.upload.initialize', $wedding), [
        'client_upload_id' => '0f7b3db7-4e4c-4b9c-9f42-e4c05bd9349d',
        'upload_token' => str_repeat('a', 64),
        'file_name' => 'too-large.mp4',
        'size' => config('memory-wall.max_file_size') + 1,
        'mime_type' => 'video/mp4',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('size');
});

test('does not initialize an upload while the memory wall is closed', function (): void {
    $wedding = Wedding::factory()->create([
        'wedding_date' => now()->subDay(),
        'memory_wall_open_until' => now()->subMinute(),
    ]);

    $this->postJson(route('memory-wall.upload.initialize', $wedding), [
        'client_upload_id' => '0f7b3db7-4e4c-4b9c-9f42-e4c05bd9349d',
        'upload_token' => str_repeat('a', 64),
        'file_name' => 'photo.jpg',
        'size' => 1024,
        'mime_type' => 'image/jpeg',
    ])->assertForbidden();
});

test('queues completion instead of processing the upload in the HTTP request', function (): void {
    $wedding = openMemoryWallWedding();
    $token = str_repeat('d', 64);
    $upload = MemoryWallUpload::factory()->for($wedding)->create([
        'upload_token_hash' => hash('sha256', $token),
        'status' => MemoryWallUploadStatus::Uploading,
        'multipart_upload_id' => 'multipart-upload-id',
    ]);
    Queue::fake();

    $this->postJson(route('memory-wall.upload.complete', [$wedding, $upload]), [
        'upload_token' => $token,
    ])->assertAccepted()
        ->assertJsonPath('data.status', 'processing');

    Queue::assertPushed(
        CompleteMemoryWallUploadJob::class,
        fn (CompleteMemoryWallUploadJob $job): bool => $job->upload->is($upload)
            && $job->wedding->is($wedding),
    );

    expect($upload->fresh()->status)->toBe(MemoryWallUploadStatus::Uploading);
});

test('does not queue another completion while an upload is processing', function (): void {
    $wedding = openMemoryWallWedding();
    $token = str_repeat('f', 64);
    $upload = MemoryWallUpload::factory()->for($wedding)->create([
        'upload_token_hash' => hash('sha256', $token),
        'status' => MemoryWallUploadStatus::Processing,
    ]);
    Queue::fake();

    $this->postJson(route('memory-wall.upload.complete', [$wedding, $upload]), [
        'upload_token' => $token,
    ])->assertAccepted()
        ->assertJsonPath('data.status', 'processing');

    Queue::assertNothingPushed();
});

test('returns the completed media for an already completed upload', function (): void {
    Storage::fake('public');
    $wedding = openMemoryWallWedding();
    $media = $wedding->addMedia(UploadedFile::fake()->image('memory.jpg'))
        ->toMediaCollection('MemoryWall', 'public');
    $token = str_repeat('e', 64);
    $upload = MemoryWallUpload::factory()->for($wedding)->create([
        'media_id' => $media->id,
        'upload_token_hash' => hash('sha256', $token),
        'status' => MemoryWallUploadStatus::Completed,
    ]);
    Queue::fake();

    $this->postJson(route('memory-wall.upload.complete', [$wedding, $upload]), [
        'upload_token' => $token,
    ])->assertOk()
        ->assertJsonPath('data.id', $media->id);
});

test('does not repeat completion for an upload already completed by a previous job attempt', function (): void {
    $wedding = openMemoryWallWedding();
    $upload = MemoryWallUpload::factory()->for($wedding)->create([
        'status' => MemoryWallUploadStatus::Completed,
    ]);
    $completion = app(CompleteMemoryWallUpload::class);

    (new CompleteMemoryWallUploadJob($wedding, $upload))
        ->handle($completion, app(Cleanup::class));

    expect($upload->fresh()->status)->toBe(MemoryWallUploadStatus::Completed);
});

test('creates wedding media through media library after every part and metadata are verified', function (): void {
    $originalMediaDisk = config('memory-wall.media_disk');
    $originalConversionsDisk = config('memory-wall.conversions_disk');
    $originalMediaDiskConfig = config('filesystems.disks.memory-wall-media');
    $originalConversionsDiskConfig = config('filesystems.disks.memory-wall-conversions');

    try {
        config([
            'memory-wall.media_disk' => 'memory-wall-media',
            'memory-wall.conversions_disk' => 'memory-wall-conversions',
            'filesystems.disks.memory-wall-media' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/disks/memory-wall-media'),
            ],
            'filesystems.disks.memory-wall-conversions' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/disks/memory-wall-conversions'),
            ],
        ]);
        Storage::fake('memory-wall-media');
        Storage::fake('memory-wall-conversions');

        $wedding = openMemoryWallWedding();
        $sourceFile = UploadedFile::fake()->image('memory.jpg');
        $sourceContents = file_get_contents($sourceFile->getRealPath());
        $sourceSize = strlen($sourceContents);
        $objectPath = "memory-wall/pending/{$wedding->uuid}/upload.jpg";
        Storage::disk('memory-wall-media')->put($objectPath, $sourceContents);

        $token = str_repeat('b', 64);
        $upload = MemoryWallUpload::factory()->for($wedding)->create([
            'media_id' => null,
            'upload_token_hash' => hash('sha256', $token),
            'object_path' => $objectPath,
            'original_name' => 'memory.jpg',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'expected_size' => $sourceSize,
            'part_size' => $sourceSize,
            'total_parts' => 1,
            'status' => MemoryWallUploadStatus::Uploading,
            'multipart_upload_id' => 'multipart-upload-id',
        ]);
        $storage = mock(MemoryWallMultipartStorage::class);
        $storage->shouldReceive('listParts')->once()->andReturn([
            ['part_number' => 1, 'etag' => 'etag', 'size' => $sourceSize],
        ]);
        $storage->shouldReceive('completeMultipartUpload')->once();
        $storage->shouldReceive('objectMetadata')->once()->andReturn([
            'size' => $sourceSize,
            'mime_type' => 'image/jpeg',
        ]);
        $this->app->instance(MemoryWallMultipartStorage::class, $storage);
        Event::fake([MediaHasBeenAddedEvent::class, MemoryWallUploadProcessed::class]);
        Queue::fake();

        $completedMedia = app(MemoryWallUploadService::class)->complete($wedding, $upload, $token);

        expect($completedMedia->model_type)->toBe($wedding->getMorphClass())
            ->and($completedMedia->model_id)->toBe($wedding->id)
            ->and($completedMedia->collection_name)->toBe('MemoryWall')
            ->and($completedMedia->disk)->toBe('memory-wall-media')
            ->and($completedMedia->conversions_disk)->toBe('memory-wall-conversions')
            ->and($upload->fresh()->media_id)->toBe($completedMedia->id)
            ->and($upload->fresh()->status)->toBe(MemoryWallUploadStatus::Completed)
            ->and(Storage::disk('memory-wall-media')->missing($objectPath))->toBeTrue();

        Event::assertDispatched(
            MediaHasBeenAddedEvent::class,
            fn (MediaHasBeenAddedEvent $event): bool => $event->media->is($completedMedia),
        );
        $upload->forceFill([
            'media_id' => null,
            'status' => MemoryWallUploadStatus::Processing,
        ])->save();

        (new CompleteMemoryWallUploadJob($wedding, $upload))
            ->handle(
                app(CompleteMemoryWallUpload::class),
                app(Cleanup::class),
            );

        expect($wedding->media()->where('collection_name', 'MemoryWall')->count())->toBe(1)
            ->and($upload->fresh()->media_id)->toBe($completedMedia->id)
            ->and($upload->fresh()->status)->toBe(MemoryWallUploadStatus::Completed);

        Event::assertDispatched(
            MemoryWallUploadProcessed::class,
            fn (MemoryWallUploadProcessed $event): bool => $event->uploadUuid === $upload->uuid
                && $event->status === MemoryWallUploadStatus::Completed
                && $event->media['id'] === $completedMedia->id,
        );
    } finally {
        config([
            'memory-wall.media_disk' => $originalMediaDisk,
            'memory-wall.conversions_disk' => $originalConversionsDisk,
            'filesystems.disks.memory-wall-media' => $originalMediaDiskConfig,
            'filesystems.disks.memory-wall-conversions' => $originalConversionsDiskConfig,
        ]);
    }
});

test('does not expose an upload session through another wedding', function (): void {
    $wedding = openMemoryWallWedding();
    $otherWedding = openMemoryWallWedding();
    $token = str_repeat('c', 64);
    $upload = MemoryWallUpload::factory()->for($otherWedding)->create([
        'upload_token_hash' => hash('sha256', $token),
        'status' => MemoryWallUploadStatus::Uploading,
        'multipart_upload_id' => 'multipart-upload-id',
    ]);
    $storage = mock(MemoryWallMultipartStorage::class);
    $storage->shouldNotReceive('temporaryPartUrls');
    $this->app->instance(MemoryWallMultipartStorage::class, $storage);

    $this->postJson(route('memory-wall.upload.parts', [$wedding, $upload]), [
        'upload_token' => $token,
    ])->assertNotFound();
});
