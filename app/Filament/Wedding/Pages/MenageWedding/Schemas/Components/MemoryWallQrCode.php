<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\MenageWedding\Schemas\Components;

use App\Models\Wedding;
use App\Services\MemoryWallService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
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
        return TextEntry::make('memory_wall_qr_code')
            ->label(__('QR Code'))
            ->visible(fn (Get $get): bool => (bool) $get('has_memory_wall'))
            ->stateCast(function (?Wedding $record): HtmlString {
                if (! $record) {
                    return new HtmlString('');
                }

                return new HtmlString(app(MemoryWallService::class)->generateQrCode($record));
            })
            ->hintAction(
                Action::make('download')
                    ->label(__('Download'))
                    ->icon(Heroicon::ArrowDownTray)
                    ->form([
                        Select::make('format')
                            ->label(__('Format'))
                            ->options([
                                'svg' => 'SVG',
                                'png' => 'PNG',
                            ])
                            ->default('svg')
                            ->required(),
                    ])
                    ->action(function (Wedding $record, array $data): Response {
                        $format = $data['format'] ?? 'svg';
                        $qrCode = app(MemoryWallService::class)->getQrCode($record, $format);

                        $contentType = $format === 'svg' ? 'image/svg+xml' : 'image/png';

                        return response()->streamDownload(
                            fn () => print($qrCode),
                            "qr-code-{$record->uuid}.{$format}",
                            ['Content-Type' => $contentType]
                        );
                    })
            );
    }
}
