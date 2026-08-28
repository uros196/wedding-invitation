<?php

/*
|--------------------------------------------------------------------------
| Memory Wall upload settings
|--------------------------------------------------------------------------
|
| Size values are expressed in bytes because the same limits are validated
| by Laravel and sent to the browser for its early, user-friendly checks.
| The actual file bytes are uploaded directly to the configured S3-compatible
| disk in multipart parts; Laravel only coordinates the session and final
| validation.
|
*/

return [

    // Original memory wall files are stored on this disk.
    'media_disk' => env('MEMORY_WALL_MEDIA_DISK', 's3'),

    // Generated media conversions are stored on this disk.
    'conversions_disk' => env('MEMORY_WALL_CONVERSIONS_DISK', 's3'),

    // Keep the upper limit at 1 GiB for large videos from modern phones.
    'max_file_size' => 1024 * 1024 * 1024,

    // S3 requires all non-final parts to be at least 5 MiB; 8 MiB is a safe default.
    'part_size' => 8 * 1024 * 1024,

    // Presigned part URLs must remain valid while a large upload is in progress.
    'presigned_url_minutes' => 120,

    // Number of completed media items shown in the random public preview.
    'preview_limit' => 10,

    // Maximum number of files that one wedding can accept from the drop zone.
    'max_files' => 20,

    // Extensions are checked together with the MIME mapping below; neither is trusted alone.
    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'heic',
        'heif',
        'mp4',
        'mov',
        'avi',
        'webm',
        'm4v',
    ],
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/heic',
        'image/heif',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/webm',
        'video/x-m4v',
    ],
    // A single extension can legitimately have more than one MIME representation.
    'extension_mime_types' => [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'heic' => ['image/heic', 'image/heif'],
        'heif' => ['image/heic', 'image/heif'],
        'mp4' => ['video/mp4'],
        'mov' => ['video/quicktime'],
        'avi' => ['video/x-msvideo'],
        'webm' => ['video/webm'],
        'm4v' => ['video/x-m4v', 'video/mp4'],
    ],
];
