<div {{ $attributes->merge(['class' => 'flex flex-col gap-4']) }}>
    {{-- Keep the preview title and unsaved-state indicator together. --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex flex-col gap-1">
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                {{ __('wedding.manage_wedding.meta.preview.heading') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('wedding.manage_wedding.meta.preview.description') }}
            </p>
        </div>

        @if($hasUnsavedChanges)
            <span
                class="inline-flex shrink-0 rounded-full bg-warning-100 p-1.5 text-warning-700 dark:bg-warning-950 dark:text-warning-300"
                title="{{ __('wedding.manage_wedding.meta.preview.unsaved_tooltip') }}"
                aria-label="{{ __('wedding.manage_wedding.meta.preview.unsaved_tooltip') }}"
            >
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
            </span>
        @endif
    </div>

    {{-- Keep the preview note reusable so callers can opt out when needed. --}}
    @if($showNote)
        <x-filament::callout color="info" icon="heroicon-o-information-circle" role="note">
            <x-slot:heading>
                {{ __('wedding.manage_wedding.meta.preview.callout.heading') }}
            </x-slot:heading>
            <x-slot:description>
                {{ __('wedding.manage_wedding.meta.preview.callout.description') }}
            </x-slot:description>
        </x-filament::callout>
    @endif

    @if($platforms !== [])
        <div
            class="flex min-w-0 flex-col gap-3"
            x-data="{ activeTab: @js($platforms[0]->value) }"
        >
            {{-- Filament provides the accessible tab controls; Alpine switches the visible card. --}}
            <x-filament::tabs
                :label="__('wedding.manage_wedding.meta.preview.tabs_label')"
                class="w-full overflow-x-auto"
            >
                @foreach($platforms as $platform)
                    <x-filament::tabs.item
                        id="{{ $platform->tabId() }}"
                        aria-controls="{{ $platform->panelId() }}"
                        alpine-active="activeTab === '{{ $platform->value }}'"
                        x-bind:aria-selected="activeTab === '{{ $platform->value }}'"
                        x-on:click="activeTab = '{{ $platform->value }}'"
                    >
                        {{ $platform->label() }}
                    </x-filament::tabs.item>
                @endforeach
            </x-filament::tabs>

            {{-- Each platform owns its card markup so app-specific layouts stay isolated. --}}
            @foreach($platforms as $platform)
                <div
                    id="{{ $platform->panelId() }}"
                    class="min-w-0"
                    role="tabpanel"
                    aria-labelledby="{{ $platform->tabId() }}"
                    x-cloak
                    x-show="activeTab === '{{ $platform->value }}'"
                >
                    <x-dynamic-component
                        :component="$platform->viewComponent()"
                        :meta-data="$metaData"
                        :url-host="$urlHost"
                    />
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
            {{ __('wedding.manage_wedding.meta.preview.empty') }}
        </div>
    @endif

    {{-- Explain why the user must save before the public preview can change. --}}
    @if($hasUnsavedChanges)
        <x-filament::callout color="warning" icon="heroicon-o-exclamation-triangle" role="status">
            <x-slot:description>
                {{ __('wedding.manage_wedding.meta.preview.unsaved') }}
            </x-slot:description>
        </x-filament::callout>
    @endif
</div>
