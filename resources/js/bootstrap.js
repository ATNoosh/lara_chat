import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

// Ensure sender does not receive their own broadcast when using toOthers()
// by attaching the current socket ID to outgoing requests
axios.interceptors.request.use((config) => {
    try {
        if (window.Echo && typeof window.Echo.socketId === 'function') {
            const socketId = window.Echo.socketId();
            if (socketId) {
                config.headers = config.headers || {};
                config.headers['X-Socket-Id'] = socketId;
            }
        }
    } catch (e) {
        // no-op
    }
    return config;
});
