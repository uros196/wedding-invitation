<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use App\DTOs\MetaData;
use App\Enums\OpenGraphPlatform;
use App\Models\Wedding;
use App\Support\MetaFactory;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;

/**
 * Adapt the reusable Open Graph preview for a Filament schema.
 *
 * The adapter keeps form-state handling in PHP while the Blade component
 * remains responsible only for presenting the already prepared preview data.
 */
final class OpenGraphPreview
{
    /**
     * Create a preview using persisted metadata by default.
     *
     * Set `$updateOnStateChange` to `true` when the preview should use the
     * current form values before they are saved.
     *
     * @param  array<int, OpenGraphPlatform|string>|null  $platforms
     */
    public static function make(?array $platforms = null, bool $showNote = true, bool $updateOnStateChange = false): View
    {
        return View::make('filament.schemas.components.open-graph-preview')
            ->viewData(static fn (Get $get, ?Wedding $record, MetaFactory $metaFactory): array => self::viewData(
                $get,
                $record,
                $metaFactory,
                $platforms,
                $showNote,
                $updateOnStateChange,
            ))
            ->columnSpanFull();
    }

    /**
     * Resolve the data passed to the reusable Blade component.
     *
     * The preview can use either persisted metadata or the current form state,
     * while unsaved changes are reported independently of that choice.
     *
     * @param  array<int, OpenGraphPlatform|string>|null  $platforms
     * @return array{metaData: MetaData, url: null, hasUnsavedChanges: bool, platforms: array<int, OpenGraphPlatform|string>|null, showNote: bool}
     */
    private static function viewData(
        Get $get,
        ?Wedding $record,
        MetaFactory $metaFactory,
        ?array $platforms,
        bool $showNote,
        bool $updateOnStateChange,
    ): array {
        $savedMetaData = $metaFactory->forWedding($record);

        return [
            'metaData' => $updateOnStateChange
                ? self::makePreviewMetaData($get, $savedMetaData, $metaFactory)
                : $savedMetaData,
            'url' => null,
            'hasUnsavedChanges' => self::hasUnsavedChanges($get, $record),
            'platforms' => $platforms,
            'showNote' => $showNote,
        ];
    }

    /**
     * Combine editable form values with the saved image and default values.
     */
    private static function makePreviewMetaData(Get $get, MetaData $savedMetaData, MetaFactory $metaFactory): MetaData
    {
        return $metaFactory->make([
            'title' => self::stringState($get('meta_title')),
            'description' => self::stringState($get('meta_description')),
            'image' => $savedMetaData->image,
        ]);
    }

    /**
     * Determine whether the form contains metadata that has not been saved.
     */
    private static function hasUnsavedChanges(Get $get, ?Wedding $record): bool
    {
        if (self::valueChanged($get('meta_title'), $record?->meta_title)) {
            return true;
        }

        if (self::valueChanged($get('meta_description'), $record?->meta_description)) {
            return true;
        }

        $persistedMediaState = $record
            ? $record->getMedia('MetaImage')->pluck('uuid')->all()
            : [];

        return self::normalizeMediaState($get('MetaImage')) !== self::normalizeMediaState($persistedMediaState);
    }

    /**
     * Compare two scalar form values while treating empty values as equal.
     */
    private static function valueChanged(mixed $current, mixed $saved): bool
    {
        if (blank($current) && blank($saved)) {
            return false;
        }

        return (string) $current !== (string) $saved;
    }

    /**
     * Keep only string form state values accepted by the metadata factory.
     */
    private static function stringState(mixed $state): ?string
    {
        return is_string($state) ? $state : null;
    }

    /**
     * Normalize media state before comparing current and persisted uploads.
     *
     * Filament can represent an upload as either a single value or an array;
     * sorting the normalized values makes the comparison independent of order.
     *
     * @return array<int, string>
     */
    private static function normalizeMediaState(mixed $state): array
    {
        $values = is_array($state) ? array_values($state) : [$state];

        return collect($values)
            ->map(static function (mixed $value): string {
                if ($value === null) {
                    return '';
                }

                return is_scalar($value) ? (string) $value : get_debug_type($value);
            })
            ->filter()
            ->sort()
            ->values()
            ->all();
    }
}
