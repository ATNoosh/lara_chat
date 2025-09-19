import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Initialize Echo with dynamic token
const initializeEcho = () => {
    const token = localStorage.getItem('token');
    
    if (window.Echo) {
        window.Echo.disconnect();
    }
    
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY || 'local',
        wsHost: import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
        wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
        forceTLS: false,
        encrypted: false,
        enabledTransports: ['ws'],
        disableStats: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                Authorization: token ? `Bearer ${token}` : undefined,
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
    });
};

// Initialize Echo only if token exists
const token = localStorage.getItem('token');
if (token) {
    initializeEcho();
}

// Export function to reinitialize Echo after login
window.initializeEcho = initializeEcho;
