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
                ->label(__('First Name')),
            ExportColumn::make('last_name')
                ->label(__('Last Name')),
            ExportColumn::make('group.name')
                ->label(__('Group')),
            ExportColumn::make('parent.fullName')
                ->label(__('Companion for')),
            ExportColumn::make('notes')
                ->label(__('Notes')),
            ExportColumn::make('age_label')
                ->label(__('Age')),
            ExportColumn::make('gender_label')
                ->label(__('Gender')),
            ExportColumn::make('created_at')
                ->label(__('Created At'))
                ->enabledByDefault(false),
            ExportColumn::make('updated_at')
                ->label(__('Updated At'))
                ->enabledByDefault(false),
        ];
    }

    /**
     * Generate the notification body for a completed export operation.
     */
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('Guest export has finished and ').Number::format($export->successful_rows).' '.__($export->successful_rows === 1 ? 'red' : 'red').' '.__('was successfully exported.');

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.__($failedRowsCount === 1 ? 'red' : 'red').' '.__('failed to export.');
        }

        return $body;
    }
}
