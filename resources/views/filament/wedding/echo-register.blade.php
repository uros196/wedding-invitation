@if (filled($user))
    <script>
        (() => {
            const channelName = @js($user->team->broadcastChannelName());
            // const listenerKey = `message-sidebar-listener:${channelName}`;

            // if (window[listenerKey]) {
            //     return
            // }

            // window[listenerKey] = true;

            {{-- Enable Echo listeners only if broadcast is implemented --}}
            @if (config('broadcasting.default') !== 'log')
                // Register listeners for the team channel
                window.Echo
                    .private(channelName)
                    .listen('.messageReceived', () => {
                        Livewire.dispatch('refresh-sidebar')
                    })
                    .listen('.attendanceConfirmed', () => {
                        Livewire.dispatch('refresh-guest-status-widget')
                    })
            @endif
        })()
    </script>
@endif
