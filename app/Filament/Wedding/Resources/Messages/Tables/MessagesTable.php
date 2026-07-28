<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Messages\Tables;

use App\Filament\Columns\CreatedAtColumn;
use App\Models\Message;
use App\Notifications\NewMessageReceived;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MessagesTable
{
    /**
     * Configure the given table with columns, filters, sorting, record actions, and toolbar actions.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $user = auth()->user();
                $messagesTable = (new Message)->getTable();

                $messageIdExpression = match ($query->getConnection()->getDriverName()) {
                    'sqlite' => "json_extract(notifications.data, '$.message_id')",
                    'mysql', 'mariadb' => "JSON_UNQUOTE(JSON_EXTRACT(notifications.data, '$.message_id'))",
                    'pgsql' => "notifications.data->>'message_id'",
                };

                $query->select("{$messagesTable}.*")
                    ->selectRaw(
                        "EXISTS (
                            SELECT 1
                            FROM notifications
                            WHERE notifications.notifiable_type = ?
                              AND notifications.notifiable_id = ?
                              AND notifications.type = ?
                              AND notifications.read_at IS NULL
                              AND $messageIdExpression = {$messagesTable}.id
                        ) AS is_unread",
                        [
                            $user::class,
                            $user->getKey(),
                            NewMessageReceived::class,
                        ],
                    );
            })
            ->columns([
                TextColumn::make('group.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content')
                    ->limit(50)
                    ->searchable(),
                IconColumn::make('is_unread')
                    ->label(__('Unread'))
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope')
                    ->falseIcon('heroicon-o-envelope-open')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                CreatedAtColumn::make(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
