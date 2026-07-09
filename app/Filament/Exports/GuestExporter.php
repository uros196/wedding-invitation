<?php

namespace App\Filament\Exports;

use App\Models\Guest;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class GuestExporter extends Exporter
{
    protected static ?string $model = Guest::class;

    /**
     * Get available columns for export.
     *
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID')
                ->enabledByDefault(false),
            ExportColumn::make('first_name')
                ->label('Ime'),
            ExportColumn::make('last_name')
                ->label('Prezime'),
            ExportColumn::make('group.name')
                ->label('Grupa'),
            ExportColumn::make('parent.fullName')
                ->label('Pratilac za'),
            ExportColumn::make('notes')
                ->label('Napomene'),
            ExportColumn::make('age_label')
                ->label('Uzrast'),
            ExportColumn::make('gender_label')
                ->label('Pol'),
            ExportColumn::make('created_at')
                ->label('Kreirano')
                ->enabledByDefault(false),
            ExportColumn::make('updated_at')
                ->label('Ažurirano')
                ->enabledByDefault(false),
        ];
    }

    /**
     * Generate the notification body for a completed export operation.
     */
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Eksport gostiju je završen i '.Number::format($export->successful_rows).' '.str('red')->plural($export->successful_rows).' je uspešno izvezeno.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('red')->plural($failedRowsCount).' nije uspelo da se izveze.';
        }

        return $body;
    }
}
