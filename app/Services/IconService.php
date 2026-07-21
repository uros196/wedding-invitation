<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CustomIcon;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IconService
{
    /**
     * Get the selectable Lucide icon options, optionally filtered by a search term.
     */
    public function getOptions(?string $search = null): Collection
    {
        $lucideIcons = collect(LucideIcon::cases());
        $customIcons = collect(CustomIcon::cases());

        return $customIcons->merge($lucideIcons)
            ->when(
                filled($search),
                fn ($icons) => $icons->filter(function (LucideIcon|CustomIcon $icon) use ($search): bool {
                    return str_contains($icon->value, (Str::slug(strtolower($search))));
                }),
            )
            ->take(50)
            ->mapWithKeys(fn (LucideIcon|CustomIcon $icon): array => [$icon->value => $this->getOptionLabel($icon->value)]);
    }

    /**
     * Build the HTML label (icon preview + name) for a single icon option.
     */
    public function getOptionLabel(string $value): string
    {
        $isCustom = CustomIcon::tryFrom($value) !== null;
        $iconName = $isCustom ? $value : "lucide-$value";

        return '<span class="flex flex-row items-center gap-2 whitespace-nowrap">'
            .svg($iconName, 'h-4 w-4 shrink-0', ['style' => 'width: 24px;'])->toHtml()
            .'<span class="truncate" style="margin-left: 10px;">'.Str::headline($value).'</span>'
            .'</span>';
    }

    /**
     * Retrieve a Lucide or Custom icon based on the given icon name.
     */
    public function getIcon(?string $icon): CustomIcon|LucideIcon|null
    {
        return filled($icon)
            ? CustomIcon::tryFrom($icon) ?? LucideIcon::tryFrom($icon)
            : null;
    }
}
