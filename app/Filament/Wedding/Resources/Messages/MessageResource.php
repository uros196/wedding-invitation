<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\Messages;

use App\Concerns\RelationScopedResource;
use App\Filament\Wedding\Resources\Messages\Pages\ManageMessages;
use App\Filament\Wedding\Resources\Messages\Pages\ViewMessage;
use App\Filament\Wedding\Resources\Messages\Schemas\MessageInfolist;
use App\Filament\Wedding\Resources\Messages\Tables\MessagesTable;
use App\Models\Message;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessageResource extends Resource
{
    use RelationScopedResource;

    /**
     * Representing model.
     */
    protected static ?string $model = Message::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    /**
     * Get the name of the relationship to scope the resource by.
     */
    protected static function getScopeRelation(): string
    {
        return 'group.wedding';
    }

    /**
     * Get the key of the related model to scope by.
     */
    protected static function getRelatedKey(): string|int|null
    {
        return auth()->user()?->team?->wedding?->id;
    }

    /**
     * Get a translatable model name.
     */
    public static function getModelLabel(): string
    {
        return __('Message');
    }

    /**
     * Get a translatable plural model name.
     */
    public static function getPluralModelLabel(): string
    {
        return __('Messages');
    }

    /**
     * Configure the infolist.
     */
    public static function infolist(Schema $schema): Schema
    {
        return MessageInfolist::configure($schema);
    }

    /**
     * Configure the table.
     */
    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    /**
     * Get the pages for the resource.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageMessages::route('/'),
            'view' => ViewMessage::route('/{record}'),
        ];
    }
}
