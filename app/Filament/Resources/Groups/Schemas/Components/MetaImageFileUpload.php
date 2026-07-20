<?php

namespace App\Filament\Resources\Groups\Schemas\Components;

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

        return SpatieMediaLibraryFileUpload::make('meta_image')
            ->label(__('Meta Image'))
            ->helperText(__('Optional. If left empty, the wedding meta data will be used.'))
            ->collection('meta_image')
            ->image()
            ->imageEditor()
            ->imageAspectRatio($aspectRatios->toArray())
            ->imageEditorAspectRatioOptions($aspectRatios->toArray());
    }
}
