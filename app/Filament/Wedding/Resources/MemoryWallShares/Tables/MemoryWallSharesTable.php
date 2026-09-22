<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallShares\Tables;

use App\Models\MemoryWallShare;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webbingbrasil\FilamentCopyActions\Actions\CopyAction;

class MemoryWallSharesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->state(fn (MemoryWallShare $record): string => $record->expires_at?->isPast()
                        ? __('Expired')
                        : __('Active'))
                    ->badge()
                    ->color(fn (MemoryWallShare $record): string => $record->expires_at?->isPast()
                        ? 'danger'
                        : 'success'),
                IconColumn::make('password')
                    ->label(__('Protected'))
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),
                IconColumn::make('allow_downloads')
                    ->label(__('Allow Downloads'))
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->label(__('Expires At'))
                    ->dateTime()
                    ->placeholder(__('No Expiry'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label(__('Preview'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (MemoryWallShare $record): string => self::getShareUrl($record))
                    ->openUrlInNewTab(),
                CopyAction::make('copy_link')
                    ->label(__('Copy Link'))
                    ->icon(Heroicon::OutlinedClipboard)
                    ->copyable(fn (MemoryWallShare $record): string => self::getShareUrl($record))
                    ->successNotificationTitle(__('messages.link_copied')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Generate the public URL through the named route contract.
     */
    private static function getShareUrl(MemoryWallShare $record): string
    {
        return route('memory-wall.share.show', ['share' => $record]);
    }
}
