<template>
    <div class="h-screen overflow-hidden bg-gray-100">
        <!-- Minimized Chat Button -->
        <div v-if="isMinimized" class="fixed bottom-4 right-4 z-50">
            <button 
                @click="toggleMinimize"
                class="bg-blue-500 text-white p-4 rounded-full shadow-lg hover:bg-blue-600 transition-colors"
                title="Open Chat"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </button>
        </div>

        <!-- Chat Window -->
        <div v-else :class="['flex', isEmbedded ? 'h-full' : 'h-screen']">
            <!-- Sidebar -->
            <div :class="['bg-white border-r border-gray-200 transition-all duration-300', sidebarCollapsed ? 'w-16' : 'w-1/3']">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div v-if="!sidebarCollapsed">
                            <h1 class="text-xl font-semibold text-gray-800">Chat App</h1>
                            <div class="mt-1 text-sm text-gray-600">Welcome, {{ user.name }}</div>
                        </div>
                        <div v-else class="flex items-center space-x-2">
                            <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" title="Toggle Sidebar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button v-if="!sidebarCollapsed" @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" title="Collapse Sidebar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button @click="toggleMinimize" class="p-2 text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" title="Minimize">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <button @click="logout" class="text-sm text-red-600 hover:text-red-700 border border-red-200 px-3 py-1 rounded-md">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Chat Groups List -->
                <div class="p-4">
                    <div v-if="!sidebarCollapsed">
                        <button 
                            @click="showCreateGroupModal = true"
                            class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            Start New Chat
                        </button>
                        <button 
                            @click="showCreateMultiGroupModal = true"
                            class="w-full mt-2 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors"
                        >
                            New Group Chat
                        </button>
                    </div>
                    <div v-else class="space-y-2">
                        <button 
                            @click="showCreateGroupModal = true"
                            class="w-full bg-blue-500 text-white py-2 px-2 rounded-lg hover:bg-blue-600 transition-colors"
                            title="Start New Chat"
                        >
                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </button>
                        <button 
                            @click="showCreateMultiGroupModal = true"
                            class="w-full bg-green-600 text-white py-2 px-2 rounded-lg hover:bg-green-700 transition-colors"
                            title="New Group Chat"
                        >
                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="overflow-y-auto">
                    <div 
                        v-for="group in chatGroups" 
                        :key="group.id"
                        @click="selectChatGroup(group)"
                        :class="[
                            'cursor-pointer hover:bg-gray-50 transition-colors',
                            selectedGroup?.id === group.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '',
                            sidebarCollapsed ? 'p-2' : 'p-4 border-b border-gray-100'
                        ]"
                    >
                        <div v-if="!sidebarCollapsed" class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-600">
                                    {{ group.type === 'FACE_TO_FACE' ? '👤' : '👥' }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">
                                    {{ getGroupName(group) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ group.type === 'FACE_TO_FACE' ? 'Direct Message' : 'Group Chat' }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex justify-center">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center" :title="getGroupName(group)">
                                <span class="text-sm font-medium text-gray-600">
                                    {{ group.type === 'FACE_TO_FACE' ? '👤' : '👥' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chat Area -->
            <div class="flex-1 flex flex-col min-h-0">
                <div v-if="selectedGroup" class="flex-1 flex flex-col min-h-0">
                    <!-- Chat Header -->
                    <div class="bg-white border-b border-gray-200 p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-600">
                                    {{ selectedGroup.type === 'FACE_TO_FACE' ? '👤' : '👥' }}
                                </span>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    {{ getGroupName(selectedGroup) }}
                                </h2>
                                <p class="text-sm text-gray-500">
                                    {{ selectedGroup.members?.length || 0 }} members
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 min-h-0" @scroll="onMessagesScroll">
                        <div 
                            v-for="message in messages" 
                            :key="message.id"
                            :ref="el => setMessageRef(el, message.id)"
                            :class="[
                                'flex',
                                message.sender_id === user.id ? 'justify-end' : 'justify-start'
                            ]"
                        >
                            <div 
                                :class="[
                                    'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                                    message.sender_id === user.id 
                                        ? 'bg-blue-500 text-white' 
                                        : 'bg-gray-200 text-gray-800'
                                ]"
                            >
                                <div class="text-sm">{{ message.text }}</div>
                                <div 
                                    :class="[
                                        'flex items-center justify-between mt-1',
                                        message.sender_id === user.id ? 'text-blue-100' : 'text-gray-500'
                                    ]"
                                >
                                    <span class="text-xs">{{ formatTime(message.created_at) }}</span>
                                    <div v-if="message.sender_id === user.id" class="flex items-center space-x-1">
                                        <span v-if="message.status === 'sent'" class="text-xs">✓</span>
                                        <span v-else-if="message.status === 'delivered'" class="text-xs">✓✓</span>
                                        <span v-else-if="message.status === 'read'" class="text-xs text-blue-200">✓✓</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Typing Indicator -->
                        <div v-if="typingUsers.length > 0" class="flex justify-start">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-gray-200 text-gray-800">
                                <div class="flex items-center space-x-2">
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">
                                        {{ typingUsers.length === 1 ? typingUsers[0].name : `${typingUsers.length} people` }} {{ typingUsers.length === 1 ? 'is' : 'are' }} typing...
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message Input (sticky bottom) -->
                    <div class="bg-white border-t border-gray-200 p-4 sticky bottom-0">
                        <form @submit.prevent="sendMessage" class="flex space-x-2">
                            <input
                                v-model="newMessage"
                                @input="onMessageInput"
                                @keydown="onMessageKeydown"
                                @keyup="onMessageKeyup"
                                type="text"
                                placeholder="Type a message..."
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <button
                                type="submit"
                                :disabled="!newMessage.trim()"
                                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Send
                            </button>
                        </form>
                        
                    </div>
                </div>
                
                <!-- No Chat Selected -->
                <div v-else class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-6xl text-gray-300 mb-4">💬</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Select a chat to start messaging</h3>
                        <p class="text-gray-500">Choose a conversation from the sidebar or start a new one</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Create Group Modal (Face to Face) -->
        <div v-if="showCreateGroupModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-96">
                <h3 class="text-lg font-semibold mb-4">Start New Chat</h3>
                <form @submit.prevent="createChatGroup">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select User
                        </label>
                        <select 
                            v-model="selectedUserId"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Choose a user...</option>
                            <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button
                            type="button"
                            @click="showCreateGroupModal = false"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="!selectedUserId"
                            class="flex-1 bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 disabled:opacity-50"
                        >
                            Start Chat
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Multi-User Group Modal -->
        <div v-if="showCreateMultiGroupModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-[30rem]">
                <h3 class="text-lg font-semibold mb-4">Create Group Chat</h3>
                <form @submit.prevent="createMultiUserGroup">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Group Name (optional)
                        </label>
                        <input v-model="newGroupName" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Awesome Group" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Members
                        </label>
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                            <div v-for="u in availableUsers" :key="u.id" class="flex items-center px-3 py-2 border-b last:border-b-0">
                                <input type="checkbox" class="mr-3" :value="u.id" v-model="selectedMemberIds" />
                                <div class="text-sm">
                                    <div class="text-gray-900">{{ u.name }}</div>
                                    <div class="text-gray-500">{{ u.email }}</div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">You will be added automatically.</p>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" @click="showCreateMultiGroupModal = false" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">Cancel</button>
                        <button type="submit" :disabled="selectedMemberIds.length === 0" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 disabled:opacity-50">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import axios from 'axios'
import Echo from 'laravel-echo'

const user = ref({})
const chatGroups = ref([])
const selectedGroup = ref(null)
const messages = ref([])
const newMessage = ref('')
const showCreateGroupModal = ref(false)
const showCreateMultiGroupModal = ref(false)
const selectedUserId = ref('')
const selectedMemberIds = ref([])
const newGroupName = ref('')
const availableUsers = ref([])
const messagesContainer = ref(null)
let currentChannel = null
let currentChannelGroupId = null
const isUserNearBottom = ref(true)
const isMinimized = ref(false)
const isEmbedded = ref(false)
const sidebarCollapsed = ref(false)

// Intersection Observer for auto-read messages
const messageRefs = ref(new Map())
let intersectionObserver = null

// Typing status
const typingUsers = ref([])
let typingTimeout = null
let isTyping = ref(false)

// Toggle minimize/maximize
const toggleMinimize = () => {
    isMinimized.value = !isMinimized.value
}

// Toggle sidebar collapse
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value
}

// Logout: clear token, disconnect Echo, redirect to login
const logout = () => {
    try {
        localStorage.removeItem('token')
        if (axios?.defaults?.headers?.common) {
            delete axios.defaults.headers.common['Authorization']
        }
        if (window.Echo) {
            try { window.Echo.disconnect() } catch (e) {}
        }
    } catch (e) {}
    window.location.href = '/login'
}

// Load user data
const loadUser = async () => {
    try {
        const response = await axios.get('/api/user')
        user.value = response.data
    } catch (error) {
        console.error('Error loading user:', error)
    }
}

// Load chat groups
const loadChatGroups = async () => {
    try {
        const response = await axios.get('/api/chat_groups')
        chatGroups.value = response.data.data
    } catch (error) {
        console.error('Error loading chat groups:', error)
    }
}

// Load available users for creating new chats
const loadAvailableUsers = async () => {
    try {
        const response = await axios.get('/api/users')
        availableUsers.value = response.data.data
    } catch (error) {
        console.error('Error loading users:', error)
    }
}

// Select chat group
const selectChatGroup = async (group) => {
    // Stop typing in previous group
    if (isTyping.value && selectedGroup.value?.uuid) {
        updateTypingStatus(false)
    }
    
    // Clear typing users list
    typingUsers.value = []
    
    selectedGroup.value = group
    await loadMessages(group.uuid)
    setupEchoListener(group.uuid)
}

// Load messages for selected group
const loadMessages = async (groupUuid) => {
    try {
        const response = await axios.get(`/api/chat_groups/${groupUuid}/messages`)
        messages.value = response.data.data
        await nextTick()
        scrollToBottom()
        
        // Setup intersection observer for auto-read messages
        setupIntersectionObserver()
        
        // Mark messages as read when loading
        markMessagesAsRead(groupUuid)
    } catch (error) {
        console.error('Error loading messages:', error)
    }
}

// Mark messages as read
const markMessagesAsRead = async (groupUuid) => {
    if (!groupUuid) return
    
    try {
        await axios.post(`/api/chat_groups/${groupUuid}/messages/read`)
    } catch (error) {
        console.error('Error marking messages as read:', error)
    }
}

// Mark single message as read
const markMessageAsRead = async (messageId) => {
    if (!selectedGroup.value?.uuid) return
    
    try {
        await axios.post(`/api/chat_groups/${selectedGroup.value.uuid}/messages/${messageId}/read`)
    } catch (error) {
        console.error('Error marking message as read:', error)
    }
}

// Set message ref for intersection observer
const setMessageRef = (el, messageId) => {
    if (el) {
        messageRefs.value.set(messageId, el)
    } else {
        messageRefs.value.delete(messageId)
    }
}

// Setup intersection observer for auto-read messages
const setupIntersectionObserver = () => {
    if (!('IntersectionObserver' in window)) {
        console.warn('IntersectionObserver not supported')
        return
    }

    // Clean up existing observer
    if (intersectionObserver) {
        intersectionObserver.disconnect()
    }

    intersectionObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const messageId = parseInt(entry.target.dataset.messageId)
                    const message = messages.value.find(m => m.id === messageId)
                    
                    // Only mark as read if:
                    // 1. Message is not from current user
                    // 2. Message is not already read
                    // 3. Message is not sent by current user
                    if (message && message.sender_id !== user.value.id && message.status !== 'read') {
                        markMessageAsRead(messageId)
                        // Update local status immediately for better UX
                        message.status = 'read'
                        message.read_at = new Date().toISOString()
                    }
                }
            })
        },
        {
            root: messagesContainer.value,
            rootMargin: '0px',
            threshold: 0.5 // Message must be 50% visible
        }
    )

    // Observe all message elements
    messageRefs.value.forEach((el, messageId) => {
        if (el && el.dataset) {
            el.dataset.messageId = messageId
            intersectionObserver.observe(el)
        }
    })
}

// Typing functions
const updateTypingStatus = async (typing) => {
    if (!selectedGroup.value?.uuid) return
    
    try {
        const response = await axios.post(`/api/chat_groups/${selectedGroup.value.uuid}/typing`, {
            is_typing: typing
        })
        isTyping.value = typing
    } catch (error) {
        console.error('Error updating typing status:', error)
    }
}

const onMessageInput = () => {
    if (!isTyping.value) {
        updateTypingStatus(true)
    }
    
    // Clear existing timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout)
    }
    
    // Set timeout to stop typing after 2 seconds of inactivity
    typingTimeout = setTimeout(() => {
        if (isTyping.value) updateTypingStatus(false)
    }, 2000)
}

const onMessageKeydown = (event) => {
    // Handle Enter key
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        sendMessage()
        // Stop typing when message is sent
        if (isTyping.value) {
            updateTypingStatus(false)
        }
        if (typingTimeout) {
            clearTimeout(typingTimeout)
        }
    }
}

const onMessageKeyup = (event) => {
    // Handle Escape key to stop typing
    if (event.key === 'Escape' && isTyping.value) {
        updateTypingStatus(false)
        if (typingTimeout) {
            clearTimeout(typingTimeout)
        }
    }
}

// (debug helpers removed)

// Send message
const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedGroup.value) return
    
    try {
        const response = await axios.post(`/api/chat_groups/${selectedGroup.value.uuid}/messages`, {
            message: newMessage.value
        })
        
        messages.value.push(response.data.data)
        newMessage.value = ''
        await nextTick()
        scrollToBottom()
    } catch (error) {
        console.error('Error sending message:', error)
    }
}

// Create new chat group
const createChatGroup = async () => {
    if (!selectedUserId.value) return
    
    try {
        const response = await axios.post('/api/chat_groups', {
            secondUserId: selectedUserId.value
        })
        
        chatGroups.value.push(response.data.data)
        selectedGroup.value = response.data.data
        showCreateGroupModal.value = false
        selectedUserId.value = ''
        await loadMessages(response.data.data.uuid)
        // Wait a bit for Echo to be ready, then setup listener
        await nextTick()
        setTimeout(() => setupEchoListener(response.data.data.uuid), 100)
    } catch (error) {
        console.error('Error creating chat group:', error)
    }
}

// Create multi-user chat group
const createMultiUserGroup = async () => {
    if (selectedMemberIds.value.length === 0) return
    try {
        const response = await axios.post('/api/chat_groups', {
            name: newGroupName.value || undefined,
            memberIds: selectedMemberIds.value,
        })

        chatGroups.value.push(response.data.data)
        selectedGroup.value = response.data.data
        showCreateMultiGroupModal.value = false
        selectedMemberIds.value = []
        newGroupName.value = ''
        await loadMessages(response.data.data.uuid)
        // Wait a bit for Echo to be ready, then setup listener
        await nextTick()
        setTimeout(() => setupEchoListener(response.data.data.uuid), 100)
    } catch (error) {
        console.error('Error creating multi-user chat group:', error)
    }
}

// Setup Echo listener for real-time messages
const setupEchoListener = (groupUuid) => {
    if (!window.Echo) {
        console.warn('Echo not available, retrying in 500ms...')
        setTimeout(() => setupEchoListener(groupUuid), 500)
        return
    }

    // Avoid duplicate listeners by leaving previous channel
    if (currentChannel && currentChannelGroupId && currentChannelGroupId !== groupUuid) {
        window.Echo.leave(`chat.${currentChannelGroupId}`)
        currentChannel = null
        currentChannelGroupId = null
    }

    if (currentChannelGroupId === groupUuid && currentChannel) {
        return
    }

    try {
        currentChannel =         window.Echo
            .private(`chat.${groupUuid}`)
            .stopListening('MessageSent')
            .listen('MessageSent', (e) => {
                messages.value.push(e.message)
                nextTick(() => {
                    if (isUserNearBottom.value) {
                        scrollToBottom()
                    }
                    // Setup intersection observer for new message
                    setupIntersectionObserver()
                })
            })
            .stopListening('MessagesRead')
            .listen('MessagesRead', (e) => {
                // Update message statuses
                e.messageIds.forEach(messageId => {
                    const message = messages.value.find(m => m.id === messageId)
                    if (message) {
                        message.status = 'read'
                        message.read_at = e.readAt
                    }
                })
            })
            .stopListening('UserTyping')
            .listen('UserTyping', (e) => {
                // Update typing users list
                if (e.is_typing) {
                    // Add user to typing list if not already there
                    if (!typingUsers.value.find(u => u.id === e.user.id)) {
                        typingUsers.value.push(e.user)
                    }
                } else {
                    // Remove user from typing list
                    typingUsers.value = typingUsers.value.filter(u => u.id !== e.user.id)
                }
            })
        currentChannelGroupId = groupUuid
    } catch (error) {
        console.error('Failed to setup Echo listener:', error)
    }
}

// Get group name
const getGroupName = (group) => {
    if (group.type === 'FACE_TO_FACE') {
        const otherMember = group.members?.find(member => member.id !== user.value.id)
        return otherMember ? otherMember.name : 'Unknown User'
    }
    if (group.name) return group.name
    // Compose from first few members
    const names = (group.members || []).map(m => m.name).filter(Boolean)
    return names.length ? names.slice(0, 3).join(', ') + (names.length > 3 ? '…' : '') : 'Group Chat'
}

// Format time
const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// Scroll to bottom (robust + smooth)
const scrollToBottom = () => {
    const el = messagesContainer.value
    if (!el) return
    // Use double rAF to ensure layout is settled, then smooth scroll
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (typeof el.scrollTo === 'function') {
                el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' })
            } else {
                el.scrollTop = el.scrollHeight
            }
        })
    })
}

// Track if user is near the bottom to avoid forcing scroll while reading older messages
const onMessagesScroll = () => {
    const el = messagesContainer.value
    if (!el) return
    const threshold = 80 // px from bottom considered as near bottom
    isUserNearBottom.value = el.scrollHeight - el.scrollTop - el.clientHeight < threshold
}

// Watch for selected group changes
// Removed watcher re-subscribing to prevent duplicate listeners

onMounted(async () => {
    // Check if embedded mode (URL parameter or parent window)
    isEmbedded.value = new URLSearchParams(window.location.search).get('embedded') === 'true' || 
                      window.parent !== window
    
    await loadUser()
    await loadChatGroups()
    await loadAvailableUsers()
    // Subscribe to current user's private channel for group additions
    if (window.Echo && user.value?.id) {
        window.Echo.private(`App.Models.User.${user.value.id}`)
            .stopListening('GroupAdded')
            .listen('GroupAdded', (e) => {
                // Avoid duplicates
                if (!chatGroups.value.find(g => g.id === e.group.id)) {
                    chatGroups.value.push(e.group)
                }
            })
    }
})

onUnmounted(() => {
    // Clean up intersection observer
    if (intersectionObserver) {
        intersectionObserver.disconnect()
        intersectionObserver = null
    }
    
    // Clean up typing timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout)
        typingTimeout = null
    }
    
    // Stop typing if currently typing
    if (isTyping.value && selectedGroup.value?.uuid) {
        updateTypingStatus(false)
    }
})
</script>


