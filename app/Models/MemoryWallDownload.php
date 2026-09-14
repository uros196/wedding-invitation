<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemoryWallDownloadStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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
     */
    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Get the user who requested the archive.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the immutable media entries included in this archive snapshot.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MemoryWallDownloadItem::class);
    }

    /**
     * Scope a query to ready archives whose temporary URL has expired.
     */
    #[Scope]
    protected function readyExpired(Builder $query, CarbonInterface $at): void
    {
        $query->where('status', MemoryWallDownloadStatus::Ready)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $at);
    }

    /**
     * Scope a query to terminal downloads that still have storage artifacts.
     */
    #[Scope]
    protected function terminalWithArtifacts(Builder $query): void
    {
        $query->whereIn('status', [
            MemoryWallDownloadStatus::Cancelled,
            MemoryWallDownloadStatus::Expired,
            MemoryWallDownloadStatus::Failed,
        ])->where(function (Builder $query): void {
            $query->whereNotNull('archive_path')
                ->orWhereNotNull('multipart_upload_id');
        });
    }

    /**
     * Scope a query to cancellation requests that have exceeded their grace period.
     */
    #[Scope]
    protected function cancellingBefore(Builder $query, CarbonInterface $before): void
    {
        $query->where('status', MemoryWallDownloadStatus::Cancelling)
            ->whereNotNull('cancellation_requested_at')
            ->where('cancellation_requested_at', '<=', $before);
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
