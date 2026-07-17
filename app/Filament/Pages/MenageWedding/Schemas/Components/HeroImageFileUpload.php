<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding\Schemas\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class HeroImageFileUpload
{
    /**
     * Generate the file upload for the hero image.
     */
    public static function make(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('hero')
            ->hiddenLabel()
            ->collection('hero')
            ->image()
            ->imageEditor()
            ->columnSpanFull();
    }
}
