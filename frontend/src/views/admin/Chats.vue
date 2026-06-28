<template>
  <div class="min-h-screen from-purple-50 via-pink-50 to-blue-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
      <div class="glass-card rounded-3xl p-8 mb-8">
        <h1 class="heading-lg mb-2">Чаты с клиентами</h1>
        <p class="text-body">Управление заказами и общение с клиентами</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
          <div class="glass-card-strongest rounded-2xl overflow-hidden">
            <div class="p-4 bg-gradient-to-r from-purple-600 to-pink-600">
              <h2 class="text-lg font-bold text-white">Список чатов</h2>
              <p class="text-sm text-purple-100 mt-1">{{ chats.length }} активных</p>
            </div>

            <div v-if="loading" class="p-8 text-center">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-purple-600"></div>
              <p class="mt-3 text-gray-600 text-sm">Загрузка...</p>
            </div>

            <div v-else-if="chats.length === 0" class="p-8 text-center">
              <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              <p class="mt-4 text-gray-600 font-semibold">Нет активных чатов</p>
            </div>

            <div v-else class="divide-y divide-gray-200">
              <button
                v-for="chat in chats"
                :key="chat.id"
                @click="selectChat(chat)"
                :class="[
                  'w-full text-left p-4 transition-colors hover:bg-purple-50',
                  selectedChat?.id === chat.id ? 'bg-purple-100' : 'bg-white/60'
                ]"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <p class="font-semibold text-gray-900 truncate">
                        {{ chat.user.name || chat.user.email }}
                      </p>
                      <span v-if="chat.unreadCount > 0" class="bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5">
                        {{ chat.unreadCount }}
                      </span>
                    </div>
                    <p v-if="chat.lastMessage" class="text-sm text-gray-600 truncate">
                      {{ chat.lastMessage.hasOrder ? 'Новый заказ!' : chat.lastMessage.text }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ formatDate(chat.lastMessageAt) }}
                    </p>
                  </div>
                  <svg class="w-5 h-5 text-gray-400 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </button>
            </div>
          </div>
        </div>

        <div class="lg:col-span-2">
          <div v-if="!selectedChat" class="glass-card-strongest rounded-2xl p-12 text-center">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="text-gray-600 font-semibold">Выберите чат для просмотра</p>
          </div>

          <div v-else class="glass-card-strongest rounded-2xl overflow-hidden">
            <div class="p-4 bg-gradient-to-r from-purple-600 to-pink-600">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-white">{{ selectedChat.user.name || selectedChat.user.email }}</h3>
                  <div class="flex flex-col gap-0.5 text-sm text-purple-100">
                    <span v-if="selectedChat.user.phone">{{ selectedChat.user.phone }}</span>
                    <span>{{ selectedChat.user.email }}</span>
                    <span class="text-xs">ID: {{ selectedChat.user.id }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white/30">
              <ChatWindow
                ref="chatWindow"
                :chat="selectedChat"
                :messages="messages"
                :loading="loadingMessages"
                :sending-message="sendingMessage"
                :show-header="false"
                @send-message="handleSendMessage"
                @order-updated="handleOrderUpdated"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useChatStore } from '@/stores/chat'
import ChatWindow from '@/components/ChatWindow.vue'

const chatStore = useChatStore()

const selectedChat = ref(null)
const loading = ref(true)
const loadingMessages = ref(false)
const pollingInterval = ref(null)
const chatWindow = ref(null)

const chats = computed(() => chatStore.chats)
const messages = computed(() => chatStore.messages)
const sendingMessage = computed(() => chatStore.sendingMessage)

const loadChats = async () => {
  loading.value = true
  try {
    await chatStore.fetchChatList()
  } catch (error) {
    console.error('Ошибка загрузки чатов:', error)
  } finally {
    loading.value = false
  }
}

const selectChat = async (chat) => {
  selectedChat.value = chat
  loadingMessages.value = true
  
  try {
    await chatStore.fetchMessages(chat.id)
    await chatStore.markAsRead(chat.id)
    
    const chatIndex = chats.value.findIndex(c => c.id === chat.id)
    if (chatIndex !== -1) {
      chats.value[chatIndex].unreadCount = 0
    }
    
    // Прокручиваем вниз после загрузки сообщений
    await nextTick()
    setTimeout(() => {
      chatWindow.value?.scrollToBottom()
    }, 100)
  } catch (error) {
    console.error('Ошибка загрузки сообщений:', error)
  } finally {
    loadingMessages.value = false
  }
}

const handleSendMessage = async (text) => {
  if (!selectedChat.value) return
  
  const result = await chatStore.sendMessage(selectedChat.value.id, text)
  
  if (!result.success) {
    alert(result.message || 'Не удалось отправить сообщение')
  }
}

const handleOrderUpdated = (updatedOrder) => {
  // Обновляем заказ в сообщениях без перезагрузки
  chatStore.updateOrderInMessages(updatedOrder)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = (now - date) / (1000 * 60 * 60)
  
  if (diffInHours < 24) {
    return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
  } else if (diffInHours < 24 * 7) {
    return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
  } else {
    return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
  }
}

const startPolling = () => {
  stopPolling()
  
  pollingInterval.value = setInterval(async () => {
    await chatStore.fetchChatList()
    
    if (selectedChat.value) {
      const oldLength = messages.value.length
      await chatStore.fetchMessages(selectedChat.value.id)
      
      const updatedChat = chats.value.find(c => c.id === selectedChat.value.id)
      if (updatedChat) {
        selectedChat.value = updatedChat
      }
    }
    
    await chatStore.fetchUnreadCount()
  }, 15000)
}

const stopPolling = () => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
    pollingInterval.value = null
  }
}

onMounted(() => {
  loadChats()
  startPolling()
})

onUnmounted(() => {
  stopPolling()
})
</script>
