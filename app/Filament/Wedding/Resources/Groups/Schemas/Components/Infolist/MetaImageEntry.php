<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Groups\Schemas\Components\Infolist;

use App\Models\Group;
use Filament\Infolists\Components\ImageEntry;

class MetaImageEntry
{
    /**
     * Generate the metadata image entry.
     */
    public static function make(): ImageEntry
    {
        return ImageEntry::make('MetaImage')
            ->label(__('messages.meta.image'))
            ->state(fn (Group $record): ?string => $record->getMetaImageUrl('preview'))
            ->imageHeight(180);
    }
}
