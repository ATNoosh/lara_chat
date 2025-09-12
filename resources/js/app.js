import './bootstrap';

import { createApp, h } from 'vue'
import { createRouter, createWebHistory, RouterView } from 'vue-router'
import ChatApp from './Pages/Chat/ChatApp.vue'
import Login from './Pages/Auth/Login.vue'
import Register from './Pages/Auth/Register.vue'
import axios from 'axios'

// Set up axios defaults
const token = localStorage.getItem('token')
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

// Router configuration
const routes = [
    { path: '/', component: Login },
    { path: '/login', component: Login },
    { path: '/register', component: Register },
    { path: '/chat', component: ChatApp, meta: { requiresAuth: true } }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')
    
    if (to.meta.requiresAuth && !token) {
        next('/login')
    } else if ((to.path === '/login' || to.path === '/register') && token) {
        next('/chat')
    } else {
        next()
    }
})

router.afterEach((to) => {
    console.log('[Debug] Navigated to route:', to.fullPath)
})

// Create Vue app with render function (no runtime template compilation)
const app = createApp({
    render: () => h(RouterView)
})

// Add error handling
app.config.errorHandler = (err, vm, info) => {
    console.error('Vue Error:', err, info)
}

app.use(router)
app.mount('#app')

console.log('Vue app mounted successfully')
