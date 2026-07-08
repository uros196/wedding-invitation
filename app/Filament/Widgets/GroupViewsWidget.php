<?php

namespace App\Filament\Widgets;

use App\Services\GroupService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Widget that displays a table with the most viewed groups.
 */
class GroupViewsWidget extends TableWidget
{
    /**
     * The title of the widget.
     */
    protected static ?string $heading = 'Pregledi po grupama';

    /**
     * The sort order of the widget on the dashboard.
     */
    protected static ?int $sort = 2;

    /**
     * Configure the table for the widget.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                app(GroupService::class)->getMostViewedGroups()
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Grupa'),

                TextColumn::make('views_count')
                    ->label('Broj pregleda')
                    ->numeric()
                    ->alignEnd(),
            ])
            ->paginated(false);
    }
}
