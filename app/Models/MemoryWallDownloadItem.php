<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Captures one source object in an immutable memory wall archive snapshot.
 *
 * Storing the original disk path and archive name prevents later upload
 * changes from affecting an already requested download.
 */
class MemoryWallDownloadItem extends Model
{
    /**
     * The source metadata copied into the snapshot.
     *
     * @var list<string>
     */
    protected $fillable = [
        'memory_wall_download_id',
        'memory_wall_upload_id',
        'disk',
        'path',
        'original_name',
        'archive_name',
        'size',
        'mime_type',
    ];

    /**
     * Cast the source size for byte arithmetic during archive preparation.
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /**
     * Get the archive request containing this item.
     *
     * @return BelongsTo<MemoryWallDownload, $this>
     */
    public function download(): BelongsTo
    {
        return $this->belongsTo(MemoryWallDownload::class, 'memory_wall_download_id');
    }

    /**
     * Get the upload session from which this source object originated.
     *
     * @return BelongsTo<MemoryWallUpload, $this>
     */
    public function upload(): BelongsTo
    {
        return $this->belongsTo(MemoryWallUpload::class, 'memory_wall_upload_id');
    }
}
