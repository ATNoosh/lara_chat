import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set up authentication token
const token = localStorage.getItem('token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

// Request interceptor to ensure token is always included
axios.interceptors.request.use((config) => {
    // Ensure token is always fresh
    const token = localStorage.getItem('token');
    if (token) {
        config.headers = config.headers || {};
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    
    // Ensure sender does not receive their own broadcast when using toOthers()
    // by attaching the current socket ID to outgoing requests
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
