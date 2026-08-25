{{-- Messenger follows the Meta family of wide link attachment previews. --}}
@props(['metaData', 'urlHost'])

<div
    class="mx-auto w-full max-w-md min-w-0 rounded-2xl bg-[#f0f2f5] p-3 shadow-sm dark:bg-[#1c1e21]"
    data-open-graph-platform="messenger"
>
    <div class="flex justify-center">
        <article class="w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            @if(filled($metaData->image))
                <div class="aspect-[1.91/1] w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <img
                        src="{{ $metaData->image }}"
                        alt="{{ __('wedding.manage_wedding.meta.preview.image_alt') }}"
                        class="block h-full w-full object-cover"
                        loading="lazy"
                    />
                </div>
            @else
                <div class="flex aspect-[1.91/1] w-full items-center justify-center bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <x-filament::icon icon="heroicon-o-photo" class="h-10 w-10" />
                    <span class="sr-only">{{ __('wedding.manage_wedding.meta.preview.image_placeholder') }}</span>
                </div>
            @endif

            <div class="flex flex-col gap-1 p-3">
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
        </article>
    </div>
</div>
