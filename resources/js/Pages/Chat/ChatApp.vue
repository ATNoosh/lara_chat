<template>
    <div class="h-screen overflow-hidden bg-gray-100">
        <div class="flex h-screen">
            <!-- Sidebar -->
            <div class="w-1/3 bg-white border-r border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h1 class="text-xl font-semibold text-gray-800">Chat App</h1>
                    <div class="mt-2 text-sm text-gray-600">
                        Welcome, {{ user.name }}
                    </div>
                </div>
                
                <!-- Chat Groups List -->
                <div class="p-4">
                    <button 
                        @click="showCreateGroupModal = true"
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        Start New Chat
                    </button>
                </div>
                
                <div class="overflow-y-auto">
                    <div 
                        v-for="group in chatGroups" 
                        :key="group.id"
                        @click="selectChatGroup(group)"
                        :class="[
                            'p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50',
                            selectedGroup?.id === group.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''
                        ]"
                    >
                        <div class="flex items-center space-x-3">
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
                                        'text-xs mt-1',
                                        message.sender_id === user.id ? 'text-blue-100' : 'text-gray-500'
                                    ]"
                                >
                                    {{ formatTime(message.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message Input (sticky bottom) -->
                    <div class="bg-white border-t border-gray-200 p-4 sticky bottom-0">
                        <form @submit.prevent="sendMessage" class="flex space-x-2">
                            <input
                                v-model="newMessage"
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
        
        <!-- Create Group Modal -->
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
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import axios from 'axios'
import Echo from 'laravel-echo'

const user = ref({})
const chatGroups = ref([])
const selectedGroup = ref(null)
const messages = ref([])
const newMessage = ref('')
const showCreateGroupModal = ref(false)
const selectedUserId = ref('')
const availableUsers = ref([])
const messagesContainer = ref(null)
let currentChannel = null
let currentChannelGroupId = null
const isUserNearBottom = ref(true)

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
    } catch (error) {
        console.error('Error loading messages:', error)
    }
}

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
        setupEchoListener(response.data.data.uuid)
    } catch (error) {
        console.error('Error creating chat group:', error)
    }
}

// Setup Echo listener for real-time messages
const setupEchoListener = (groupUuid) => {
    if (window.Echo) {
        // Avoid duplicate listeners by leaving previous channel
        if (currentChannel && currentChannelGroupId && currentChannelGroupId !== groupUuid) {
            window.Echo.leave(`chat.${currentChannelGroupId}`)
            currentChannel = null
            currentChannelGroupId = null
        }

        if (currentChannelGroupId === groupUuid && currentChannel) {
            return
        }

        currentChannel = window.Echo
            .private(`chat.${groupUuid}`)
            .stopListening('MessageSent')
            .listen('MessageSent', (e) => {
                messages.value.push(e.message)
                nextTick(() => {
                    if (isUserNearBottom.value) {
                        scrollToBottom()
                    }
                })
            })
        currentChannelGroupId = groupUuid
    }
}

// Get group name
const getGroupName = (group) => {
    if (group.type === 'FACE_TO_FACE') {
        const otherMember = group.members?.find(member => member.id !== user.value.id)
        return otherMember ? otherMember.name : 'Unknown User'
    }
    return group.name || 'Group Chat'
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
    await loadUser()
    await loadChatGroups()
    await loadAvailableUsers()
})
</script>


