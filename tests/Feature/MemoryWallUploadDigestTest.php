<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\MemoryWallUploadDigest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;

test('formats Serbian digest counts using the correct grammatical forms', function (int $count, string $images, string $videos): void {
    app()->setLocale('sr_Latn');
    $user = User::factory()->create();
    Notification::fake();

    foreach ([
        [$count, 0, "$count $images"],
        [0, $count, "$count $videos"],
        [$count, $count, "$count $images, $count $videos"],
    ] as [$imageCount, $videoCount, $media]) {
        $notification = new MemoryWallUploadDigest($imageCount, $videoCount);
        $database = $notification->toDatabase($user);
        $broadcast = $notification->toBroadcast($user)->data;

        expect($database['body'])->toBe("Na vašem zidu uspomena: $media.")
            ->and($broadcast['body'])->toBe($database['body'])
            ->and($database['image_count'])->toBe($imageCount)
            ->and($database['video_count'])->toBe($videoCount);
    }
})->with([
    [1, 'slika', 'video zapis'],
    [2, 'slike', 'video zapisa'],
    [3, 'slike', 'video zapisa'],
    [4, 'slike', 'video zapisa'],
    [5, 'slika', 'video zapisa'],
    [10, 'slika', 'video zapisa'],
    [11, 'slika', 'video zapisa'],
    [12, 'slika', 'video zapisa'],
    [14, 'slika', 'video zapisa'],
    [20, 'slika', 'video zapisa'],
    [21, 'slika', 'video zapis'],
    [22, 'slike', 'video zapisa'],
    [24, 'slike', 'video zapisa'],
    [25, 'slika', 'video zapisa'],
    [101, 'slika', 'video zapis'],
    [111, 'slika', 'video zapisa'],
    [112, 'slika', 'video zapisa'],
    [114, 'slika', 'video zapisa'],
    [121, 'slika', 'video zapis'],
    [122, 'slike', 'video zapisa'],
]);

test('formats English digest counts and omits empty media types', function (int $images, int $videos, string $media): void {
    app()->setLocale('en');
    $user = User::factory()->create();
    Notification::fake();
    $notification = new MemoryWallUploadDigest($images, $videos);

    expect($notification->toDatabase($user)['body'])->toBe("New on your Memory Wall: $media.")
        ->and($notification->toBroadcast($user)->data['body'])->toBe("New on your Memory Wall: $media.");
})->with([
    [1, 0, '1 photo'],
    [2, 0, '2 photos'],
    [0, 1, '1 video'],
    [0, 5, '5 videos'],
    [1, 1, '1 photo, 1 video'],
    [21, 22, '21 photos, 22 videos'],
]);

test('omits the digest body when both media counts are zero', function (string $locale): void {
    app()->setLocale($locale);
    $user = User::factory()->create();
    Notification::fake();
    $notification = new MemoryWallUploadDigest(0, 0);

    expect($notification->toDatabase($user)['body'])->toBeNull()
        ->and($notification->toBroadcast($user)->data['body'])->toBeNull();
})->with(['en', 'sr_Latn']);

test('keeps wedding translation keys aligned between supported locales', function (): void {
    $english = require lang_path('en/wedding.php');
    $serbian = require lang_path('sr_Latn/wedding.php');

    expect(array_keys(Arr::dot($english)))->toBe(array_keys(Arr::dot($serbian)));
});
