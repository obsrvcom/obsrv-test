import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Only initialize Echo if broadcasting is properly configured
if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wssPort: import.meta.env.VITE_REVERB_PORT,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    // Handle presence cleanup on navigation for wire:navigate
    document.addEventListener('livewire:navigating', (event) => {
        // Find any active presence channels and clean them up
        if (window.Echo && window.Echo.connector && window.Echo.connector.channels) {
            const presenceChannels = Object.keys(window.Echo.connector.channels).filter(name =>
                name.startsWith('presence-ticket-presence.')
            );

            presenceChannels.forEach(channelName => {
                console.log('🧹 Cleaning up presence channel on navigation:', channelName);
                window.Echo.leaveChannel(channelName);
            });
        }
    });

} else {
    // Fallback when broadcasting is not configured
    window.Echo = null;
    console.log('Broadcasting not configured - real-time features disabled');
}
