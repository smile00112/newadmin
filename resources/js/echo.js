import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Detect if running in local development environment
const isLocal = window.location.hostname === 'localhost' || 
               window.location.hostname === '127.0.0.1' || 
               window.location.hostname.includes('.test') || 
               window.location.hostname.includes('.local');

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT ?? (isLocal ? 8080 : 80),
    wssPort: import.meta.env.VITE_REVERB_PORT ?? (isLocal ? 8080 : 443),
    // For local development always use ws:// (forceTLS: false)
    // For production use the configured scheme (default: http, which means false)
    forceTLS: isLocal ? false : ((import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https'),
    enabledTransports: ['ws', 'wss'],
});
