<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemoryWallUploadStatus;
use App\Observers\MemoryWallUploadObserver;
use App\Policies\MemoryWallUploadPolicy;
use Database\Factories\MemoryWallUploadFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks the control-plane state for one memory wall multipart upload.
 *
 * The related media row is normally created after the assembled object passes
 * validation; direct-upload adapters may create it earlier and keep it hidden
 * until the object is complete.
 *
 * @property MemoryWallUploadStatus $status
 */
#[ObservedBy(MemoryWallUploadObserver::class)]
#[UsePolicy(MemoryWallUploadPolicy::class)]

class MemoryWallUpload extends Model
{
    /** @use HasFactory<MemoryWallUploadFactory> */
    use HasFactory;

    /**
     * The attributes that are mass-assignable.
     *
     * @var array
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
