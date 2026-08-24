<x-filament-panels::page>
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('wedding.setup.intro.heading') }}</x-slot>

            <div class="flex flex-col gap-2 text-sm text-gray-600 dark:text-gray-400">
                <p>{{ __('wedding.setup.intro.description') }}</p>
                <p>{{ __('wedding.setup.intro.duration') }}</p>
            </div>
        </x-filament::section>

        <form wire:submit="publish">
            {{ $this->form }}
        </form>

        <div class="flex justify-start">
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
