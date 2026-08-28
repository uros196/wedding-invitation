<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemoryWallUploadStatus;
use App\Observers\MemoryWallUploadObserver;
use App\Policies\MemoryWallUploadPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Tracks the control-plane state for one memory wall multipart upload.
 *
 * The related media row is created only after the assembled object passes final
 * validation and is added to the Wedding-owned Media Library collection.
 *
 * @property MemoryWallUploadStatus $status
 */
#[ObservedBy(MemoryWallUploadObserver::class)]
#[UsePolicy(MemoryWallUploadPolicy::class)]

class MemoryWallUpload extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wedding_id',
        'media_id',
        'uuid',
        'client_upload_id',
        'upload_token_hash',
        'multipart_upload_id',
        'object_path',
        'original_name',
        'extension',
        'mime_type',
        'expected_size',
        'part_size',
        'total_parts',
        'status',
        'error_message',
        'completed_at',
    ];

    /**
     * Cast persisted workflow values to the types used by the service layer.
     */
    protected function casts(): array
    {
        return [
            'status' => MemoryWallUploadStatus::class,
            'expected_size' => 'integer',
            'part_size' => 'integer',
            'total_parts' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the wedding that owns the upload session.
     */
    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Get the Media Library record created for the completed upload.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
