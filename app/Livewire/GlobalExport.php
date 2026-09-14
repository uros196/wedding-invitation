<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Filament\Exports\GuestExporter;
use App\Filament\Wedding\Resources\MemoryWallUploads\Actions\DownloadMemoryWallArchiveAction;
use App\Models\User;
use App\Services\MemoryWallService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\ExportAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
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
            ->label(__('Export Confirmed Guests'))
            ->exporter(GuestExporter::class)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->confirmed())
            ->icon(Heroicon::ArrowDownTray)
            ->color('primary');
    }

    /**
     * Render the memory wall archive export action when the feature is enabled.
     */
    public function downloadMemoryWallArchiveAction(): Action
    {
        $memoryWallEnabled = fn (): bool => $this->isMemoryWallEnabled();

        return DownloadMemoryWallArchiveAction::make()
            ->visible($memoryWallEnabled)
            ->authorize($memoryWallEnabled);
    }

    /**
     * Render the export menu.
     */
    public function exportAction(): ActionGroup
    {
        return ActionGroup::make([
            $this->exportGuestsAction(),
            $this->downloadMemoryWallArchiveAction(),
        ])
            ->livewire($this)
            ->label(__('Export'))
            ->icon(Heroicon::ArrowDownTray)
            ->color('secondary')
            ->button()
            ->dropdownPlacement('bottom-end')
            ->dropdownWidth(Width::ExtraSmall);
    }

    /**
     * Determine whether the authenticated wedding has enabled its memory wall.
     */
    protected function isMemoryWallEnabled(): bool
    {
        $user = auth()->user();

        return resolve(MemoryWallService::class)->isEnabled($user->team?->wedding);
    }

    /**
     * Render component.
     */
    public function render(): string
    {
        return <<<'BLADE'
            <div>
                {{ $this->exportAction() }}

                <x-filament-actions::modals />
            </div>
        BLADE;
    }
}
