<div class="flex w-full items-center justify-center overflow-hidden rounded-xl bg-gray-50 p-2 dark:bg-gray-950">
    <video
        class="max-h-[32rem] max-w-full rounded-lg bg-gray-950 object-contain"
        controls
        preload="metadata"
        poster="{{ $placeholderUrl }}"
    >
        @if (filled($videoUrl))
            <source src="{{ $videoUrl }}" type="{{ $mimeType }}">
        @endif
    </video>
</div>