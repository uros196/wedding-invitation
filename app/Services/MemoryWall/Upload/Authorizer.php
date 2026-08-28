<?php

declare(strict_types=1);

namespace App\Services\MemoryWall\Upload;

use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Enforces wedding ownership and the private token for anonymous upload calls.
 */
final readonly class Authorizer
{
    /**
     * Authorize an operation against an upload session.
     */
    public function authorize(Wedding $wedding, MemoryWallUpload $upload, string $uploadToken): void
    {
        if ($upload->wedding_id !== $wedding->getKey()) {
            throw (new ModelNotFoundException)->setModel(MemoryWallUpload::class, [$upload->getKey()]);
        }

        $this->ensureTokenIsValid($upload, $uploadToken);
    }

    /**
     * Compare a supplied token with the one-way hash stored for the session.
     */
    public function ensureTokenIsValid(MemoryWallUpload $upload, string $uploadToken): void
    {
        if (! hash_equals($upload->upload_token_hash, hash('sha256', $uploadToken))) {
            throw new AuthorizationException;
        }
    }
}
