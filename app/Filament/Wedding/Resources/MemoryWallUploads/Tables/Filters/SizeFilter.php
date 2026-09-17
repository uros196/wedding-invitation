<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallUploads\Tables\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Generate the memory wall upload size filter.
 */
class SizeFilter
{
    /**
     * Generate the size range filter.
     */
    public static function make(): Filter
    {
        return Filter::make('size')
            ->label(__('wedding.memory_wall.filters.size'))
            ->schema([
                self::from(),
                self::to(),
            ])
            ->columns(2)
            ->query(function (Builder $query, array $data): Builder {
                $from = is_numeric($data['from'] ?? null) ? (float) $data['from'] : null;
                $to = is_numeric($data['to'] ?? null) ? (float) $data['to'] : null;

                return $query
                    ->when(
                        $from !== null,
                        fn (Builder $query): Builder => $query->where('expected_size', '>=', (int) ceil($from * 1024 * 1024)),
                    )
                    ->when(
                        $to !== null,
                        fn (Builder $query): Builder => $query->where('expected_size', '<=', (int) floor($to * 1024 * 1024)),
                    );
            });
    }

    /**
     * Create the "from" input field for specifying the lower limit of the size range.
     */
    protected static function from(): TextInput
    {
        return TextInput::make('from')
            ->label(__('wedding.memory_wall.filters.from'))
            ->numeric()
            ->minValue(0)
            ->step(0.01)
            ->suffix(__('wedding.memory_wall.filters.mb'));
    }

    /**
     * Configure the "to" input field for specifying the upper limit of the size range.
     */
    protected static function to(): TextInput
    {
        return TextInput::make('to')
            ->label(__('wedding.memory_wall.filters.to'))
            ->numeric()
            ->minValue(0)
            ->step(0.01)
            ->suffix(__('wedding.memory_wall.filters.mb'));
    }
}
