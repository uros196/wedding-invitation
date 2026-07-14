<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Value object holding the meta (Open Graph) data used when sharing an
 * invitation link. Used on the front-end and as placeholders in the admin.
 */
final readonly class MetaData
{
    public function __construct(
        public string $title,
        public string $description,
        public ?string $image = null,
    ) {}

    /**
     * Get the metadata as an array.
     *
     * @return array{title: string, description: string, image: string|null}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
        ];
    }
}
