<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\MemoryWallMediaUrl;
use App\Http\Requests\UnlockMemoryWallShareRequest;
use App\Http\Resources\Media\ShareMediaResource;
use App\Http\Resources\MetaDataResource;
use App\Models\MemoryWallShare;
use App\Services\MemoryWallShareService;
use App\Support\MetaFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class MemoryWallShareController extends Controller
{
    public function __construct(
        private readonly MemoryWallShareService $service,
        private readonly MemoryWallMediaUrl $mediaUrl,
        private readonly MetaFactory $metaFactory,
    ) {}

    /**
     * Render a password gate or the first live cursor page for a share link.
     */
    public function show(Request $request, MemoryWallShare $share): InertiaResponse
    {
        $this->service->ensureActive($share);

        $isUnlocked = $this->service->isUnlocked($request, $share);
        $wedding = $share->wedding;

        return Inertia::render('memory-wall-share', [
            'share' => [
                'uuid' => $share->uuid,
                'name' => $share->name,
                'expiresAt' => $share->expires_at?->toIso8601String(),
            ],
            'requiresPassword' => $this->service->requiresPassword($share) && ! $isUnlocked,
            'allowDownloads' => $share->allow_downloads,
            'metaData' => MetaDataResource::make($this->metaFactory->forWedding($wedding)),
            'media' => $isUnlocked ? Inertia::scroll($this->media($share)) : null,
        ]);
    }

    /**
     * Unlock a password-protected share without returning any gallery data.
     */
    public function unlock(UnlockMemoryWallShareRequest $request, MemoryWallShare $share): RedirectResponse
    {
        if (! $this->service->unlock($request, $share, $request->password())) {
            throw ValidationException::withMessages([
                'password' => __('wedding.memory_wall.share.invalid_password'),
            ]);
        }

        return to_route('memory-wall.share.show', ['share' => $share]);
    }

    /**
     * Build the deterministic live cursor page and transform each media item.
     */
    private function media(MemoryWallShare $share): mixed
    {
        return $this->service
            ->mediaQuery($share)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate((int) config('memory-wall.share_page_size', 24))
            ->through(fn ($media): ShareMediaResource => new ShareMediaResource(
                $media,
                $share->allow_downloads,
                $this->mediaUrl,
            ));
    }
}
