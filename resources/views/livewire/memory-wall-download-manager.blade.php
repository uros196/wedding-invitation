{{-- Broadcast events refresh the manager only when the archive state changes. --}}
<div>
    @if ($downloads->isNotEmpty())
    @php
        $hasActiveDownloads = $downloads->contains(
            fn ($download): bool => $download->status->isActive(),
        );
        $managerTitle = $hasActiveDownloads
            ? __('wedding.memory_wall.download.title')
            : __('wedding.memory_wall.download.ready_title');
    @endphp
    <aside
        x-data="{
            collapsed: false,
            storageKey: 'memory-wall-download-manager-collapsed',
            collapseLabel: @js(__('wedding.memory_wall.download.collapse')),
            expandLabel: @js(__('wedding.memory_wall.download.expand')),
            downloadLabel: @js(__('wedding.memory_wall.download.download_file')),
            downloadStartedLabel: @js(__('wedding.memory_wall.download.download_started')),
            expandMessageLabel: @js(__('wedding.memory_wall.download.expand_message')),
            downloadResetDelay: 3500,
            startedDownloads: {},
            startedDownloadTimers: {},
            init() {
                this.collapsed = localStorage.getItem(this.storageKey) === 'true';
            },
            toggle() {
                this.collapsed = ! this.collapsed;
                localStorage.setItem(this.storageKey, this.collapsed ? 'true' : 'false');
            },
            markDownloadStarted(uuid) {
                if (! uuid) {
                    return;
                }

                this.startedDownloads[uuid] = true;
                clearTimeout(this.startedDownloadTimers[uuid]);

                const resetTimer = window.setTimeout(() => {
                    if (this.startedDownloadTimers[uuid] !== resetTimer) {
                        return;
                    }

                    delete this.startedDownloads[uuid];
                    delete this.startedDownloadTimers[uuid];
                }, this.downloadResetDelay);

                this.startedDownloadTimers[uuid] = resetTimer;
            },
            isDownloadStarted(uuid) {
                return this.startedDownloads[uuid] === true;
            },
        }"
        x-on:memory-wall-download-ready.window="markDownloadStarted($event.detail.uuid)"
        aria-label="{{ $managerTitle }}"
        class="pointer-events-none fixed inset-x-3 bottom-3 z-40 sm:inset-x-auto sm:bottom-6 sm:left-6"
    >
        {{-- The outer shell keeps the tray above the panel without blocking the page around it. --}}
        <div class="pointer-events-auto w-full max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-2xl border border-amber-200/80 bg-white/95 shadow-[0_20px_55px_-25px_rgb(120_53_15_/_0.45)] ring-1 ring-black/5 backdrop-blur-xl dark:border-amber-400/20 dark:bg-gray-950/95 dark:shadow-[0_20px_55px_-25px_rgb(0_0_0_/_0.8)] sm:w-[28rem]">
            {{-- The header establishes one clear identity for the whole download tray. --}}
            <header class="relative flex items-center gap-3 border-b border-gray-200/80 px-4 py-4 dark:border-white/10">
                <div aria-hidden="true" class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-amber-500 text-white shadow-sm shadow-amber-500/25">
                    <div class="absolute -right-3 -top-3 h-8 w-8 rounded-full bg-white/20 blur-md"></div>
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="relative h-5 w-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-sm font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $managerTitle }}
                    </h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        {{ trans_choice('wedding.memory_wall.download.requests', $downloads->count()) }}
                    </p>
                </div>
                @if ($hasActiveDownloads)
                    <span aria-hidden="true" class="relative flex h-2.5 w-2.5 shrink-0">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400/60"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    </span>
                @endif
                <button
                    type="button"
                    x-on:click="toggle"
                    x-bind:aria-expanded="! collapsed"
                    x-bind:aria-label="collapsed ? expandLabel : collapseLabel"
                    aria-controls="memory-wall-download-list"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                >
                    <x-filament::icon
                        icon="heroicon-o-chevron-up"
                        class="h-4 w-4 transition-transform duration-300"
                        x-bind:class="collapsed ? 'rotate-180' : ''"
                    />
                </button>
            </header>

            {{-- Each card represents one immutable archive snapshot. --}}
            <div
                id="memory-wall-download-list"
                x-cloak
                x-show="! collapsed"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="max-h-[min(60vh,32rem)] space-y-2.5 overflow-y-auto p-3 sm:p-4"
            >
                @foreach ($downloads as $download)
                    @php
                        $statusStyle = $download->status->getDownloadManagerStyle();
                        $isFailed = $download->status->is(\App\Enums\MemoryWallDownloadStatus::Failed);
                    @endphp

                    <section
                        wire:key="memory-wall-download-{{ $download->uuid }}"
                        class="group rounded-xl border border-gray-200/80 bg-gray-50/80 p-3.5 transition-[transform,box-shadow,border-color] duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-950/5 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-amber-400/30 dark:hover:shadow-black/20"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $statusStyle['iconClass'] }}">
                                <x-filament::icon icon="{{ $statusStyle['icon'] }}" class="h-4 w-4 {{ $download->status->isActive() ? 'animate-spin' : '' }}" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="min-w-0 truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="{{ $download->archive_name }}">
                                        {{ $download->archive_name }}
                                    </p>
                                    <div class="flex shrink-0 items-center gap-1.5">
                                        <span class="rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] {{ $statusStyle['badgeClass'] }}">
                                            {{ $download->status->getLabel() }}
                                        </span>
                                        @if ($download->status->isActive() || $download->status->canBeRemoved())
                                            <button
                                                type="button"
                                                wire:click="mountAction('removeDownload', { uuid: '{{ $download->uuid }}' })"
                                                wire:loading.attr="disabled"
                                                x-on:click.stop
                                                aria-label="{{ $download->status->isActive() ? __('wedding.memory_wall.download.cancel') : __('wedding.memory_wall.download.delete') }}"
                                                title="{{ $download->status->isActive() ? __('wedding.memory_wall.download.cancel') : __('wedding.memory_wall.download.delete') }}"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-50 hover:text-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 disabled:cursor-wait disabled:opacity-50 dark:text-gray-500 dark:hover:bg-rose-400/10 dark:hover:text-rose-400"
                                            >
                                                <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if ($isFailed)
                                    <details class="group/error mt-3">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-2 rounded-lg border border-rose-200/70 bg-rose-50/70 px-3 py-2 text-xs font-medium text-rose-700 marker:hidden dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300">
                                            <span x-text="expandMessageLabel">{{ __('wedding.memory_wall.download.expand_message') }}</span>
                                            <x-filament::icon icon="heroicon-o-chevron-down" class="h-4 w-4 transition-transform group-open/error:rotate-180" />
                                        </summary>
                                        <p class="mt-3 text-xs leading-relaxed text-rose-600 dark:text-rose-400">
                                            {{ $download->error_message ?: __('wedding.memory_wall.download.failed') }}
                                        </p>
                                    </details>
                                @elseif ($download->status->isActive())
                                    {{-- Active requests expose preparation progress in bytes and percent. --}}
                                    <div class="mt-3" aria-live="polite">
                                        <div class="flex items-center justify-between gap-3 text-[11px] text-gray-500 dark:text-gray-400">
                                            <span>{{ \Illuminate\Support\Number::fileSize($download->processed_bytes) }} / {{ \Illuminate\Support\Number::fileSize($download->total_bytes) }}</span>
                                            <span class="font-semibold tabular-nums text-amber-600 dark:text-amber-400">{{ $download->progressPercentage() }}%</span>
                                        </div>
                                        <div
                                            class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200/80 dark:bg-white/10"
                                            role="progressbar"
                                            aria-label="{{ $download->archive_name }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                            aria-valuenow="{{ $download->progressPercentage() }}"
                                        >
                                            <div
                                                class="h-full rounded-full bg-amber-500 transition-[width] duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]"
                                                style="width: {{ $download->progressPercentage() }}%"
                                            ></div>
                                        </div>
                                    </div>
                                {{-- The browser is sent to storage only after the archive is complete. --}}
                                @elseif ($download->status->isReady())
                                    <a
                                        href="{{ $this->downloadUrl($download) }}"
                                        x-on:click="markDownloadStarted('{{ $download->uuid }}')"
                                        x-bind:aria-busy="isDownloadStarted('{{ $download->uuid }}')"
                                        x-bind:aria-label="isDownloadStarted('{{ $download->uuid }}') ? downloadStartedLabel : downloadLabel"
                                        class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-[transform,background-color] duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] hover:bg-amber-600 active:scale-[0.98] dark:bg-white dark:text-gray-950 dark:hover:bg-amber-400"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-o-arrow-down-tray"
                                            class="h-4 w-4"
                                            x-bind:class="isDownloadStarted('{{ $download->uuid }}') ? 'animate-bounce' : ''"
                                        />
                                        <span x-text="isDownloadStarted('{{ $download->uuid }}') ? downloadStartedLabel : downloadLabel">
                                            {{ __('wedding.memory_wall.download.download_file') }}
                                        </span>
                                    </a>
                                {{-- Cancellation remains visible until the worker confirms cleanup. --}}
                                @elseif ($download->status->isCancelling())
                                    <p class="mt-3 text-xs leading-relaxed text-amber-600 dark:text-amber-400">
                                        {{ __('wedding.memory_wall.download.cancelling') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </aside>
    @endif

    {{-- Filament renders the confirmation modal for the action mounted above. --}}
    <x-filament-actions::modals />
</div>
