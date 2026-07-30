<?php

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Form;

use App\Enums\AspectRatio;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class MetaImageFileUpload
{
    /**
     * Generate a meta image file upload component.
     */
    public static function make(): SpatieMediaLibraryFileUpload
    {
        $aspectRatios = collect(AspectRatio::forMeta())->map->value;

        return SpatieMediaLibraryFileUpload::make('MetaImage')
            ->label(__('Meta Image'))
            ->helperText(__('wedding.groups.meta.fallback_description'))
            ->collection('MetaImage')
            ->image()
            ->conversion('preview')
            ->imageEditor()
            ->imageAspectRatio($aspectRatios->toArray())
            ->imageEditorAspectRatioOptions($aspectRatios->toArray());
    }
}
