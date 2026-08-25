<?php

declare(strict_types=1);

namespace App\View\Components;

use App\DTOs\MetaData;
use App\Enums\OpenGraphPlatform;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Uri;
use Illuminate\View\Component;

/**
 * Render a reusable preview of the Open Graph data for a shared link.
 */
final class OpenGraphPreview extends Component
{
    /**
     * The host displayed as the source of the shared link.
     */
    public string $urlHost;

    /**
     * The platform cards enabled for this preview.
     *
     * @var array<int, OpenGraphPlatform>
     */
    public array $platforms;

    /**
     * Create the preview component.
     *
     * If the caller does not provide a URL, the application URL is used to
     * keep the source label useful in previews rendered outside a real link.
     *
     * @param  array<int, OpenGraphPlatform|string>|null  $platforms
     */
    public function __construct(
        public MetaData $metaData,
        public ?string $url = null,
        public bool $hasUnsavedChanges = false,
        ?array $platforms = null,
        public bool $showNote = true,
    ) {
        $this->platforms = OpenGraphPlatform::normalize($platforms);
        $this->urlHost = Uri::of($url ?? (string) config('app.url'))->host()
            ?? (string) config('app.name');
    }

    /**
     * Render the reusable preview template.
     */
    public function render(): View
    {
        return view('components.open-graph-preview');
    }
}
