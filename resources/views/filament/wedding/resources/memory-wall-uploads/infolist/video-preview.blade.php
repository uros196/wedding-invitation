<div class="flex w-full justify-center">
    <video
        class="max-h-96 max-w-full rounded-lg object-contain"
        controls
        preload="metadata"
        poster="{{ $placeholderUrl }}"
    >
        @if (filled($videoUrl))
            <source src="{{ $videoUrl }}" type="{{ $mimeType }}">
        @endif
    </video>
</div>