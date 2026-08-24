<x-filament-panels::page>
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('wedding.groups.quick_create.intro.heading') }}</x-slot>

            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('wedding.groups.quick_create.intro.description') }}
            </p>
        </x-filament::section>

        <ol class="grid grid-cols-1 gap-3 sm:grid-cols-2" aria-label="{{ __('wedding.groups.quick_create.steps.label') }}">
            <li class="rounded-xl border p-4 {{ $step === 1 ? 'border-primary-500 bg-primary-50 dark:bg-primary-950' : 'border-gray-200 dark:border-gray-700' }}">
                <span class="text-sm font-medium">{{ __('wedding.groups.quick_create.steps.group') }}</span>
            </li>
            <li class="rounded-xl border p-4 {{ $step === 2 ? 'border-primary-500 bg-primary-50 dark:bg-primary-950' : 'border-gray-200 dark:border-gray-700' }}">
                <span class="text-sm font-medium">{{ __('wedding.groups.quick_create.steps.guests') }}</span>
            </li>
        </ol>

        @if($step === 1)
            <form wire:submit="createGroup" class="flex flex-col gap-6">
                {{ $this->groupForm }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" icon="heroicon-o-arrow-right" wire:loading.attr="disabled">
                        {{ __('wedding.groups.quick_create.group.create_action') }}
                    </x-filament::button>
                </div>
            </form>
        @else
            <x-filament::section>
                <x-slot name="heading">{{ __('wedding.groups.quick_create.guest.heading') }}</x-slot>

                <div class="flex flex-col gap-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('wedding.groups.quick_create.guest.description') }}
                    </p>
                    <p class="text-sm font-medium">
                        {{ __('wedding.groups.quick_create.guest.current_group', ['name' => $group->name]) }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('wedding.groups.quick_create.guest.added_count', ['count' => $group->guests->count()]) }}
                    </p>
                </div>
            </x-filament::section>

            <form wire:submit="addGuest" class="flex flex-col gap-6">
                {{ $this->guestForm }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" icon="heroicon-o-plus" wire:loading.attr="disabled">
                        {{ __('wedding.groups.quick_create.guest.add_action') }}
                    </x-filament::button>
                </div>
            </form>

            <x-filament::section>
                <x-slot name="heading">{{ __('Guests') }}</x-slot>

                @if($group->guests->isNotEmpty())
                    <ul class="flex flex-col divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($group->guests as $guest)
                            <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                <span class="font-medium">{{ $guest->full_name }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $guest->age_label }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('wedding.groups.quick_create.guest.empty') }}
                    </p>
                @endif
            </x-filament::section>

            <div class="flex flex-col justify-between gap-3 sm:flex-row">
                <x-filament::button color="gray" wire:click="createAnotherInvitation">
                    {{ __('wedding.groups.quick_create.guest.another_action') }}
                </x-filament::button>

                <x-filament::button color="success" wire:click="finish">
                    {{ __('wedding.groups.quick_create.guest.finish_action') }}
                </x-filament::button>
            </div>
        @endif
    </div>
</x-filament-panels::page>
