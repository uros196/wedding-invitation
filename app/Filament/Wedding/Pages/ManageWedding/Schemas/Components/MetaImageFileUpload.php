<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use App\Enums\AspectRatio;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class MetaImageFileUpload
{
    /**
     * Generate the file upload for the meta-image.
     */
    public static function make(): SpatieMediaLibraryFileUpload
    {
        $aspectRatios = collect(AspectRatio::forMeta())->map->value;

        return SpatieMediaLibraryFileUpload::make('MetaImage')
            ->label(__('Meta Image'))
            ->helperText(__('Optional. If left empty, the main image above will be used.'))
            ->collection('MetaImage')
            ->image()
            ->conversion('preview')
            ->imageEditor()
            ->imageAspectRatio($aspectRatios->toArray())
            ->imageEditorAspectRatioOptions($aspectRatios->toArray());
    }
}
