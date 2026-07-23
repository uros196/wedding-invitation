<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use App\Enums\QrCodeFormat;
use App\Enums\QrCodeSize;
use App\Models\Wedding;
use App\Services\MemoryWallService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\Response;

class MemoryWallQrCode
{
    /**
     * Generate the placeholder for the memory wall QR code.
     */
    public static function make(): TextEntry
    {
        $service = resolve(MemoryWallService::class);

        return TextEntry::make('memory_wall_qr_code')
            ->label(__('QR Code'))
            ->visible(fn (Get $get, ?Wedding $wedding): bool => (bool) $get('has_memory_wall') && $wedding?->exists)
            ->state(function (?Wedding $record) use ($service): HtmlString {
                $qrCode = $record?->exists ? $service->generateQrCode($record, 100) : '';
                return new HtmlString($qrCode);
            })
            ->hintAction(
                Action::make('download')
                    ->label(__('Download'))
                    ->icon(Heroicon::ArrowDownTray)
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('format')
                                    ->label(__('Format'))
                                    ->options(QrCodeFormat::class)
                                    ->default(QrCodeFormat::default())
                                    ->required(),

                                Select::make('size')
                                    ->label(__('Size'))
                                    ->options(QrCodeSize::class)
                                    ->default(QrCodeSize::default())
                                    ->required(),
                            ])
                    ])
                    ->action(fn (Wedding $record, array $data): Response =>
                        $service->downloadQrCode($record, $data['format'], $data['size']->value)
                    )
            );
    }
}
