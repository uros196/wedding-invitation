<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\MemoryWallDownloadStatus;
use App\Models\MemoryWallDownload;
use App\Models\Wedding;
use App\Services\MemoryWall\CancelMemoryWallDownload;
use App\Services\MemoryWall\DeleteMemoryWallDownload;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Displays recent archive requests and starts the browser download when ready.
 */
final class MemoryWallDownloadManager extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /**
     * @var array<int, string>
     */
    public array $announcedReadyDownloads = [];

    /**
     * Remember archives that were already ready when the panel was opened.
     */
    public function mount(): void
    {
        $this->announcedReadyDownloads = $this->downloads()
            ->where('status', MemoryWallDownloadStatus::Ready->value)
            ->pluck('uuid')
            ->all();
    }

    /**
     * Dispatch a browser event only for newly completed archives.
     */
    #[On('memory-wall-download-updated')]
    public function handleDownloadUpdated(): void
    {
        foreach ($this->downloads()->where('status', MemoryWallDownloadStatus::Ready->value) as $download) {
            if (in_array($download->uuid, $this->announcedReadyDownloads, true)) {
                continue;
            }

            $this->announcedReadyDownloads[] = $download->uuid;
            $this->dispatch('memory-wall-download-ready', url: $this->downloadUrl($download));
        }
    }

    /**
     * Build the Filament confirmation modal used for cancellation and deletion.
     */
    public function removeDownloadAction(): Action
    {
        return Action::make('removeDownload')
            ->requiresConfirmation()
            ->color('danger')
            ->modalIcon(Heroicon::OutlinedExclamationTriangle)
            ->modalHeading(fn (Action $action): string => $this->removalConfirmation($action))
            ->modalDescription(fn (Action $action): string => $this->removalDescription($action))
            ->modalSubmitActionLabel(fn (Action $action): string => $this->removalLabel($action))
            ->action(function (
                array $arguments,
                CancelMemoryWallDownload $cancelDownload,
                DeleteMemoryWallDownload $deleteDownload,
            ): void {
                $this->removeDownloadRecord(
                    (string) $arguments['uuid'],
                    $cancelDownload,
                    $deleteDownload,
                );
            });
    }

    /**
     * Cancel preparation or remove a completed archive from the current wedding.
     */
    public function removeDownload(
        string $uuid,
        CancelMemoryWallDownload $cancelDownload,
        DeleteMemoryWallDownload $deleteDownload,
    ): void {
        $this->removeDownloadRecord($uuid, $cancelDownload, $deleteDownload);
    }

    /**
     * Apply the requested lifecycle transition after the download was authorized.
     */
    private function removeDownloadRecord(
        string $uuid,
        CancelMemoryWallDownload $cancelDownload,
        DeleteMemoryWallDownload $deleteDownload,
    ): void {
        $download = $this->findDownload($uuid);

        if ($download === null) {
            return;
        }

        if ($download->status->isActive()) {
            $cancelDownload->handle($download);

            return;
        }

        if ($download->status->canBeRemoved()) {
            $deleteDownload->handle($download);
        }
    }

    /**
     * Render the latest requests belonging to the authenticated wedding.
     */
    public function render(): View
    {
        return view('livewire.memory-wall-download-manager', [
            'downloads' => $this->downloads(),
        ]);
    }

    /**
     * Build the authorized route used by the ready-state download link.
     */
    public function downloadUrl(MemoryWallDownload $download): string
    {
        $wedding = $this->wedding();
        abort_unless($wedding !== null && $download->wedding_id === $wedding->getKey(), 403);

        return route('memory-wall.downloads.file', [
            'wedding' => $wedding,
            'memoryWallDownload' => $download,
        ]);
    }

    /**
     * Resolve the download referenced by a mounted Filament action argument.
     */
    private function downloadForAction(Action $action): ?MemoryWallDownload
    {
        $uuid = $action->getArguments()['uuid'] ?? null;

        return is_string($uuid) ? $this->findDownload($uuid) : null;
    }

    /**
     * Return the confirmation heading for the current lifecycle operation.
     */
    private function removalConfirmation(Action $action): string
    {
        return $this->downloadForAction($action)?->status->isActive()
            ? __('wedding.memory_wall.download.cancel_confirmation')
            : __('wedding.memory_wall.download.delete_confirmation');
    }

    /**
     * Explain whether the archive will be cancelled or removed from storage.
     */
    private function removalDescription(Action $action): string
    {
        return $this->downloadForAction($action)?->status->isActive()
            ? __('wedding.memory_wall.download.cancel_description')
            : __('wedding.memory_wall.download.delete_description');
    }

    /**
     * Label the destructive confirmation button according to the operation.
     */
    private function removalLabel(Action $action): string
    {
        return $this->downloadForAction($action)?->status->isActive()
            ? __('wedding.memory_wall.download.cancel')
            : __('wedding.memory_wall.download.delete');
    }

    /**
     * Find a download belonging to the currently authenticated wedding.
     */
    private function findDownload(string $uuid): ?MemoryWallDownload
    {
        return $this->wedding()?->memoryWallDownloads()
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Load only recent requests that can still be shown in the manager.
     *
     * @return Collection<int, MemoryWallDownload>
     */
    private function downloads(): Collection
    {
        $wedding = $this->wedding();

        return $wedding === null
            ? collect()
            : $wedding->memoryWallDownloads()
                ->whereIn('status', array_map(
                    static fn (MemoryWallDownloadStatus $status): string => $status->value,
                    [
                        MemoryWallDownloadStatus::Queued,
                        MemoryWallDownloadStatus::Processing,
                        MemoryWallDownloadStatus::Cancelling,
                        MemoryWallDownloadStatus::Ready,
                        MemoryWallDownloadStatus::Failed,
                    ],
                ))
                ->latest()
                ->limit(5)
                ->get();
    }

    /**
     * Resolve the wedding associated with the current panel user.
     */
    private function wedding(): ?Wedding
    {
        $user = auth('wedding')->user();

        $wedding = $user?->team?->wedding;

        return $wedding instanceof Wedding ? $wedding : null;
    }
}
