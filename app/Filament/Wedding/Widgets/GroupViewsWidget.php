<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Widgets;

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
     * Get the widget heading.
     */
    public function getHeading(): string
    {
        return __('widgets.group_views.heading');
    }

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
                app(GroupService::class)->getMostViewedGroups(auth()->user())
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('widgets.group_views.columns.name')),

                TextColumn::make('views_count')
                    ->label(__('widgets.group_views.columns.views_count'))
                    ->numeric()
                    ->alignEnd(),
            ])
            ->paginated(false);
    }
}
