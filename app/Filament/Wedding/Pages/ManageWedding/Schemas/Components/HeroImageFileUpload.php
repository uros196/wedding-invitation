<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use App\Enums\AspectRatio;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class HeroImageFileUpload
{
    /**
     * Generate the file upload for the hero image.
     */
    public static function make(): SpatieMediaLibraryFileUpload
    {
        $aspectRatios = collect(AspectRatio::forHero())->map->value;

        return SpatieMediaLibraryFileUpload::make('Hero')
            ->hiddenLabel()
            ->collection('Hero')
            ->image()
            ->imageEditor()
            ->imageAspectRatio($aspectRatios->toArray())
            ->imageEditorAspectRatioOptions($aspectRatios->toArray())
            ->required()
            ->columnSpanFull();
    }
}
