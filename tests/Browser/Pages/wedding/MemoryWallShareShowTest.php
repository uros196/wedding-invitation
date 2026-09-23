<?php

declare(strict_types=1);

use App\Enums\MediaStatus;
use App\Models\Media;
use App\Models\MemoryWallShare;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

function registerBrowserMemoryWallMediaRoute(): void
{
    Route::get('/wedding/{path}', function (string $path): Response {
        if (str_ends_with($path, '.mp4')) {
            return response('', 200)->header('Content-Type', 'video/mp4');
        }

        return response()->make(
            base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            ),
            200,
            ['Content-Type' => 'image/png'],
        );
    })->where('path', '.*');
}

function addBrowserMemoryWallMedia(
    Wedding $wedding,
    UploadedFile $file,
    bool $withPreview = true,
): Media {
    $media = $wedding
        ->addMedia($file)
        ->toMediaCollection(Wedding::MEMORY_WALL_COLLECTION, 's3');

    $media->forceFill(['status' => MediaStatus::Ready])->save();

    if ($withPreview) {
        Storage::disk('s3')->copy(
            $media->getPathRelativeToRoot(),
            $media->getPathRelativeToRoot('preview'),
        );

        $media->forceFill(['generated_conversions' => ['preview' => true]])->save();
    }

    return $media;
}

test('a visitor can unlock a share and view its live gallery', function (): void {
    Storage::fake('s3');
    registerBrowserMemoryWallMediaRoute();

    $wedding = Wedding::factory()->memoryWallEnabled()->create();
    addBrowserMemoryWallMedia($wedding, UploadedFile::fake()->image('memory.jpg'));
    $share = MemoryWallShare::factory()
        ->for($wedding)
        ->passwordProtected('correct-password')
        ->create(['name' => 'Reception memories']);

    $page = $this->visit(route('memory-wall.share.show', $share));

    $page->assertSee('Ova galerija je zaštićena')
        ->assertMissing('[data-fancybox="memory-wall-gallery"]')
        ->fill('input[name="password"]', 'correct-password')
        ->press('Otključaj galeriju')
        ->assertSee('Galerija uspomena')
        ->assertPresent('[data-fancybox="memory-wall-gallery"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('a share gallery opens mixed media in Fancybox and honors downloads', function (): void {
    Storage::fake('s3');
    registerBrowserMemoryWallMediaRoute();

    $wedding = Wedding::factory()->memoryWallEnabled()->create();
    $image = addBrowserMemoryWallMedia(
        $wedding,
        UploadedFile::fake()->image('memory-image.jpg'),
    );
    $video = addBrowserMemoryWallMedia(
        $wedding,
        UploadedFile::fake()->create('memory-video.mp4', 100, 'video/mp4'),
        false,
    );
    $video->forceFill(['mime_type' => 'video/mp4'])->save();
    $share = MemoryWallShare::factory()->for($wedding)->create([
        'name' => 'All memories',
        'allow_downloads' => true,
    ]);

    $page = $this->visit(route('memory-wall.share.show', $share));

    $imageSelector = '[data-media-uuid="'.$image->uuid.'"]';
    $videoSelector = '[data-media-uuid="'.$video->uuid.'"]';

    $page->assertAttributeContains(
        $imageSelector,
        'data-download-filename',
        'memory-image.jpg',
    )->assertPresent($videoSelector)
        ->assertAttribute($videoSelector, 'data-type', 'html5video')
        ->assertAttributeContains(
            $videoSelector,
            'data-download-filename',
            'memory-video.mp4',
        )
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    $page->click($imageSelector)->assertVisible('.fancybox__container');

    $share->update(['allow_downloads' => false]);

    $page->navigate(route('memory-wall.share.show', $share))
        ->assertAttributeMissing($videoSelector, 'data-download-src')
        ->assertAttributeMissing($videoSelector, 'data-download-filename')
        ->assertNoJavaScriptErrors();
});

test('a visitor can continue through the gallery in Fancybox as more media loads', function (): void {
    Storage::fake('s3');
    registerBrowserMemoryWallMediaRoute();

    $wedding = Wedding::factory()->memoryWallEnabled()->create();

    /** @var array<int, Media> $olderMedia */
    $olderMedia = [];

    for ($index = 0; $index < 24; $index++) {
        $media = addBrowserMemoryWallMedia(
            $wedding,
            UploadedFile::fake()->image('older-memory-'.$index.'.jpg'),
        );
        $media->forceFill([
            'created_at' => now()->subMinutes(25 - $index),
        ])->save();
        $olderMedia[] = $media;
    }

    $newest = addBrowserMemoryWallMedia(
        $wedding,
        UploadedFile::fake()->image('newer-memory.jpg'),
    );
    $share = MemoryWallShare::factory()->for($wedding)->create();
    $notLoadedSelector = '[data-media-uuid="'.$olderMedia[0]->uuid.'"]';
    $notLoadedSelectorJson = json_encode($notLoadedSelector, JSON_THROW_ON_ERROR);

    $page = $this->visit(route('memory-wall.share.show', $share));

    $page->assertNotPresent($notLoadedSelector)
        ->click('[data-media-uuid="'.$newest->uuid.'"]')
        ->assertVisible('.fancybox__container');

    $nextPageLoaded = $page->script(
        <<<JS
        async () => {
            const selector = {$notLoadedSelectorJson};

            for (let index = 0; index < 24; index++) {
                document.querySelector('button[title="Next"]')?.click();

                await new Promise((resolve) => window.setTimeout(resolve, 350));

                if (document.querySelector(selector)) {
                    return true;
                }
            }

            return Boolean(document.querySelector(selector));
        }
        JS,
    );

    expect($nextPageLoaded)->toBeTrue();

    $newMediaIsVisibleInFancybox = $page->script(
        <<<JS
        async () => {
            const selector = {$notLoadedSelectorJson};

            for (let index = 0; index < 3; index++) {
                document.querySelector('button[title="Next"]')?.click();

                await new Promise((resolve) => window.setTimeout(resolve, 350));
            }

            const trigger = document.querySelector(selector);
            const activeImage = document.querySelector('.fancybox__slide.is-selected img');

            return Boolean(
                trigger && activeImage?.getAttribute('src') === trigger.getAttribute('href'),
            );
        }
        JS,
    );

    expect($newMediaIsVisibleInFancybox)->toBeTrue();

    $page->assertVisible('.fancybox__container')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('a share gallery loads its next cursor page as the visitor scrolls', function (): void {
    Storage::fake('s3');
    registerBrowserMemoryWallMediaRoute();

    $wedding = Wedding::factory()->memoryWallEnabled()->create();
    $oldest = null;

    for ($index = 0; $index < 24; $index++) {
        $media = addBrowserMemoryWallMedia(
            $wedding,
            UploadedFile::fake()->image('older-memory-'.$index.'.jpg'),
        );
        $media->forceFill([
            'created_at' => now()->subMinutes(25 - $index),
        ])->save();

        $oldest ??= $media;
    }

    $newest = addBrowserMemoryWallMedia(
        $wedding,
        UploadedFile::fake()->image('newer-memory.jpg'),
    );
    $share = MemoryWallShare::factory()->for($wedding)->create();

    $oldestSelector = '[data-media-uuid="'.$oldest->uuid.'"]';
    $newestSelector = '[data-media-uuid="'.$newest->uuid.'"]';

    $page = $this->visit(route('memory-wall.share.show', $share));

    $page->assertPresent($newestSelector)
        ->assertNotPresent($oldestSelector);

    $page->script('window.scrollTo(0, document.body.scrollHeight);');

    $page->script(
        'window.dispatchEvent(new Event("scroll"));',
    );
    $page->script(
        <<<'JS'
        (selector) => new Promise((resolve) => {
            const deadline = Date.now() + 5000;
            const check = () => {
                if (document.querySelector(selector) || Date.now() > deadline) {
                    resolve(Boolean(document.querySelector(selector)));
                    return;
                }

                window.setTimeout(check, 50);
            };

            check();
        })
        JS,
        $oldestSelector,
    );

    $page
        ->assertPresent($oldestSelector)
        ->assertPresent($newestSelector)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
