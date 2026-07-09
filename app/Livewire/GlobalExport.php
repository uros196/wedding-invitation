<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Filament\Exports\GuestExporter;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\ExportAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class GlobalExport extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    /**
     * Render export action.
     */
    public function exportGuestsAction(): Action
    {
        return ExportAction::make('exportGuests')
            ->label('Izvezi potvrđene goste')
            ->exporter(GuestExporter::class)
            ->modifyQueryUsing(fn ($query) => $query->confirmed())
            ->icon(Heroicon::ArrowDownTray)
            ->color('primary')
            ->link();
    }

    /**
     * Render component.
     */
    public function render(): string
    {
        return <<<'BLADE'
            <div>
                {{ $this->exportGuestsAction }}

                <x-filament-actions::modals />
            </div>
        BLADE;
    }
}
