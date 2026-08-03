<div @if($autoPrompt) wire:init="authenticateWithPasskey" @endif>
    @if(\Filament\Facades\Filament::getCurrentOrDefaultPanel()?->hasPlugin('filament-breezy'))
        <div>
            <div class="mb-4 flex items-center" aria-hidden="true">
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                <span class="mx-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ __('filament-breezy::default.or') }}
                </span>
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            </div>

            <x-filament::button class="w-full" color="gray" icon="heroicon-o-key" wire:click="authenticateWithPasskey">
                {{ __('filament-breezy::default.passkeys.authenticate_using_passkey.label') }}
            </x-filament::button>

            @if($message = session()->get('authenticatePasskey::message'))
                <div class="mt-2 text-sm text-danger-600">
                    {{ $message }}
                </div>
            @endif
        </div>

        @include('filament-breezy::livewire.passkeys.authenticate-script')
    @endif
</div>
