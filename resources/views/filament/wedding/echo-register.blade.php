@if (filled($user))
    <script>
        (() => {
            const channelName = @js($user->team->broadcastChannelName());
            const listenerKey = `wedding-echo-listener:${channelName}`;

            if (window[listenerKey]) {
                return
            }

            window[listenerKey] = true;

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
                    // Refresh the archive manager whenever queue progress is broadcast.
                    .listen('.memoryWallDownloadUpdated', () => {
                        Livewire.dispatch('memory-wall-download-updated')
                    })

                // Start the native browser download after the archive becomes ready.
                window.addEventListener('memory-wall-download-ready', (event) => {
                    const link = document.createElement('a')
                    link.href = event.detail.url
                    link.click()
                    link.remove()
                })
            @endif
        })()
    </script>
@endif
