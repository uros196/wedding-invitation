<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\MemoryWallShare;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final readonly class MemoryWallShareService
{
    private const string SESSION_PREFIX = 'memory_wall_share_access.';

    public function __construct(
        private MemoryWallService $memoryWallService,
    ) {}

    /**
     * Reject links whose wedding is unpublished, disabled, missing, or expired.
     */
    public function ensureActive(MemoryWallShare $share): void
    {
        $wedding = $share->wedding;

        abort_unless(
            $wedding !== null
                && $wedding->has_memory_wall
                && ($share->expires_at === null || $share->expires_at->isFuture()),
            404,
        );
    }

    /**
     * Determine whether a share has a configured password without exposing its hash.
     */
    public function requiresPassword(MemoryWallShare $share): bool
    {
        return filled($share->password);
    }

    /**
     * Check the request-bound session marker for a current password-protected share.
     */
    public function isUnlocked(Request $request, MemoryWallShare $share): bool
    {
        if (! $this->requiresPassword($share)) {
            return true;
        }

        $sessionKey = $this->sessionKey($share);
        $marker = $request->session()->get($sessionKey);

        if (! is_string($marker) || ! hash_equals($this->accessVersion($share), $marker)) {
            $request->session()->forget($sessionKey);

            return false;
        }

        return true;
    }

    /**
     * Verify a password and bind successful access to the current session version.
     */
    public function unlock(Request $request, MemoryWallShare $share, string $password): bool
    {
        $this->ensureActive($share);

        if (! $this->requiresPassword($share) || ! Hash::check($password, (string) $share->password)) {
            $request->session()->forget($this->sessionKey($share));

            return false;
        }

        $request->session()->put($this->sessionKey($share), $this->accessVersion($share));

        return true;
    }

    /**
     * Return the live, ready Memory Wall media query for this share's wedding.
     *
     * @return Builder<Media>
     */
    public function mediaQuery(MemoryWallShare $share): Builder
    {
        $mediaTable = (new Media)->getTable();

        return $this->memoryWallService
            ->visibleMediaQuery($share->wedding)
            ->where(function (Builder $query) use ($mediaTable): void {
                $query
                    ->where(function (Builder $query) use ($mediaTable): void {
                        $query->where("{$mediaTable}.mime_type", 'like', 'image/%')
                            ->whereJsonContains("{$mediaTable}.generated_conversions->preview", true);
                    })
                    ->orWhere("{$mediaTable}.mime_type", 'like', 'video/%');
            });
    }

    /**
     * Produce the version that invalidates sessions after access settings change.
     */
    private function accessVersion(MemoryWallShare $share): string
    {
        return hash('sha256', implode('|', [
            (string) $share->password,
            $share->expires_at?->getTimestamp() ?? '',
            $share->allow_downloads ? '1' : '0',
            $share->wedding_id,
            $share->wedding?->has_memory_wall ? '1' : '0',
        ]));
    }

    /**
     * Keep each share's unlock marker isolated from every other public link.
     */
    private function sessionKey(MemoryWallShare $share): string
    {
        return self::SESSION_PREFIX.$share->uuid;
    }
}
