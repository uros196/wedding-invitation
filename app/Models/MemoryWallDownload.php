<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemoryWallDownloadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Tracks one immutable memory wall archive request.
 *
 * The archive is prepared asynchronously, while these counters provide the
 * control-plane state shown in the wedding panel.
 *
 * @property MemoryWallDownloadStatus $status
 */
class MemoryWallDownload extends Model
{
    /**
     * The attributes that can be filled when a snapshot is created or updated.
     *
     * @var list<string>
     */
    protected $fillable = [
        'wedding_id',
        'user_id',
        'uuid',
        'disk',
        'archive_path',
        'multipart_upload_id',
        'archive_name',
        'status',
        'total_bytes',
        'processed_bytes',
        'total_files',
        'processed_files',
        'started_at',
        'completed_at',
        'expires_at',
        'cancellation_requested_at',
        'cancelled_at',
        'error_message',
    ];

    /**
     * Cast lifecycle values and counters to the types used by the application.
     */
    protected function casts(): array
    {
        return [
            'status' => MemoryWallDownloadStatus::class,
            'total_bytes' => 'integer',
            'processed_bytes' => 'integer',
            'total_files' => 'integer',
            'processed_files' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancellation_requested_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Get the wedding that owns this archive request.
     *
     * @return BelongsTo<Wedding, $this>
     */
    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Get the user who requested the archive.
     *
     * @return BelongsTo<User, $this>
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the immutable media entries included in this archive snapshot.
     *
     * @return HasMany<MemoryWallDownloadItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MemoryWallDownloadItem::class);
    }

    /**
     * Use the public UUID instead of the database ID in bound download routes.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Calculate the integer percentage displayed by the progress bar.
     */
    public function progressPercentage(): int
    {
        if ($this->total_bytes === 0) {
            return $this->status->isReady() ? 100 : 0;
        }

        return min(100, (int) floor(($this->processed_bytes / $this->total_bytes) * 100));
    }

    /**
     * Generate the public identifier for a new download snapshot.
     */
    public static function newUuid(): string
    {
        return Str::uuid()->toString();
    }
}
