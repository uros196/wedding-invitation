<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Resources\MemoryWallShares\Actions;

use App\Filament\Wedding\Resources\MemoryWallShares\Schemas\MemoryWallShareForm;
use App\Models\MemoryWallShare;
use App\Models\User;
use App\Models\Wedding;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class CreateMemoryWallShareAction
{
    public static function make(): Action
    {
        return Action::make('createShare')
            ->label(__('Create Link'))
            ->icon(Heroicon::Plus)
            ->modalHeading(__('Create Link'))
            ->modalSubmitActionLabel(__('Create Link'))
            ->schema(MemoryWallShareForm::components())
            ->action(function (array $data): void {
                $user = auth()->user();

                abort_unless(
                    $user instanceof User && $user->can('create', MemoryWallShare::class),
                    403,
                );

                $wedding = $user->team?->wedding;

                abort_unless($wedding instanceof Wedding, 403);

                $share = $wedding->memoryWallShares()->create([
                    'name' => filled($data['name'] ?? null) ? $data['name'] : null,
                    'password' => filled($data['password'] ?? null) ? $data['password'] : null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'allow_downloads' => (bool) ($data['allow_downloads'] ?? false),
                ]);

                Notification::make()
                    ->title(__('wedding.memory_wall.share.created_title'))
                    ->body(route('memory-wall.share.show', ['share' => $share]))
                    ->success()
                    ->send();
            });
    }
}
