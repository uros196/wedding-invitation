<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use App\Enums\CustomIcon;
use App\Services\IconService;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IconSelect
{
    /**
     * Creates and configures a new Icon Select instance.
     */
    public static function make(string $name): Select
    {
        $service = resolve(IconService::class);

        return Select::make($name)
            ->label(__('Icon'))
            ->placeholder(__('Select an icon'))
            ->native(false)
            ->searchable()
            ->allowHtml()
            ->getSearchResultsUsing(fn (string $search): Collection => $service->getOptions($search))
            ->getOptionLabelUsing(fn (?string $value): ?string => filled($value) ? Str::headline($value) : null)
            ->live()
            ->prefixIcon(fn (?string $state): CustomIcon|LucideIcon|null => $service->getIcon($state));
    }
}
