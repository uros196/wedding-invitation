{{-- Telegram often uses a compact card with a square thumbnail beside the text. --}}
@props(['metaData', 'urlHost'])

<div
    class="mx-auto w-full max-w-md min-w-0 rounded-2xl bg-[#e6f2fa] p-3 shadow-sm dark:bg-[#182b36]"
    data-open-graph-platform="telegram"
>
    <article class="flex items-stretch gap-3 rounded-xl border border-black/5 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex min-w-0 flex-1 flex-col justify-center gap-1">
            <p class="truncate text-[0.65rem] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ $urlHost }}
            </p>
            <p class="line-clamp-2 text-sm font-semibold text-gray-950 dark:text-white">
                {{ $metaData->title }}
            </p>
            <p class="line-clamp-2 text-xs leading-4 text-gray-600 dark:text-gray-400">
                {{ $metaData->description }}
            </p>
        </div>

        @if(filled($metaData->image))
            <div class="aspect-square w-24 shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                <img
                    src="{{ $metaData->image }}"
                    alt="{{ __('wedding.manage_wedding.meta.preview.image_alt') }}"
                    class="block h-full w-full object-cover"
                    loading="lazy"
                />
            </div>
        @else
            <div class="flex aspect-square w-24 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <x-filament::icon icon="heroicon-o-photo" class="h-8 w-8" />
                <span class="sr-only">{{ __('wedding.manage_wedding.meta.preview.image_placeholder') }}</span>
            </div>
        @endif
    </article>
</div>