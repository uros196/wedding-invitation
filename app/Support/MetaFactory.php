<?php

declare(strict_types=1);

namespace App\Support;

use App\DTOs\MetaData;
use App\Models\Group;
use App\Models\Wedding;

/**
 * Builds Meta value objects using configuration defaults, allowing overrides
 * from the wedding and group records.
 */
class MetaFactory
{
    /**
     * Build metadata from configuration defaults, allowing overrides.
     *
     * @param  array{title?: string|null, description?: string|null, image?: string|null}  $overrides
     */
    public function make(array $overrides = []): MetaData
    {
        return new MetaData(
            title: (string) $this->firstFilled($overrides['title'] ?? null, __(config('wedding.meta.title'))),
            description: (string) $this->firstFilled($overrides['description'] ?? null, __(config('wedding.meta.description'))),
            image: $this->firstFilled($overrides['image'] ?? null, config('wedding.meta.image')),
        );
    }

    /**
     * Build metadata for the wedding, falling back to configuration defaults.
     */
    public function forWedding(?Wedding $wedding = null): MetaData
    {
        return $this->make([
            'title' => $wedding?->meta_title,
            'description' => $wedding?->meta_description,
            'image' => $wedding?->getMetaImageUrl(),
        ]);
    }

    /**
     * Build metadata for a specific group, falling back to the wedding data.
     */
    public function forGroup(?Group $group): MetaData
    {
        $wedding = $group?->wedding;
        $weddingMeta = $this->forWedding($wedding);

        return new MetaData(
            title: (string) $this->firstFilled($group?->meta_title, $weddingMeta->title),
            description: (string) $this->firstFilled($group?->meta_description, $weddingMeta->description),
            image: $this->firstFilled($group?->getMetaImageUrl(), $weddingMeta->image),
        );
    }

    /**
     * Get the first filled value from the given values.
     */
    private function firstFilled(?string ...$values): ?string
    {
        return array_find($values, fn ($value) => filled($value));
    }
}
