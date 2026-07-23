<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\MenageWedding\Schemas\Components;

use App\Models\Wedding;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Webbingbrasil\FilamentCopyActions\Actions\CopyAction;

class MemoryWallUrlInput
{
    /**
     * Generate the input for the memory wall URL.
     */
    public static function make(): TextInput
    {
        return TextInput::make('memory_wall_url')
            ->label(__('Memory Wall URL'))
            ->readonly()
            ->visible(fn (Get $get, ?Wedding $wedding): bool => (bool) $get('has_memory_wall') && $wedding?->exists)
            ->afterStateHydrated(function (TextInput $component, ?Wedding $record) {
                if (filled($record)) {
                    $component->state($record->memory_wall_url);
                }
            })
            ->suffixAction(CopyAction::make());
    }
}
