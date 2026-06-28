<template>
  <div class="flex flex-col h-full">
    <div v-if="loading" class="flex-1 flex items-center justify-center my-10">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-purple-600 mb-4"></div>
        <p class="text-gray-600">Загрузка чата...</p>
      </div>
    </div>

    <div v-else-if="!chat" class="flex-1 flex items-center justify-center my-10">
      <div class="text-center max-w-md">
        <svg class="w-20 h-20 mx-auto mb-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <h3 class="heading-md mb-3">{{ emptyTitle }}</h3>
        <p class="text-body">{{ emptyMessage }}</p>
      </div>
    </div>

    <template v-else>
      <div v-if="showHeader" class="glass-card-strongest p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900">{{ chatTitle }}</h3>
              <p class="text-sm text-gray-500">{{ chatSubtitle }}</p>
            </div>
          </div>
          <div v-if="chat.unreadCount > 0" class="bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
            {{ chat.unreadCount }}
          </div>
        </div>
      </div>

      <div ref="messagesContainer" class="flex-1 overflow-y-auto space-y-4 py-4 px-2 min-h-[400px] max-h-[600px]">
        <div
          v-for="message in messages"
          :key="message.id"
          :class="[
            'flex',
            message.isMine ? 'justify-end' : 'justify-start'
          ]"
        >
          <div
            :class="[
              'max-w-[80%]',
              message.isMine 
                ? 'bg-purple-600 text-white rounded-2xl rounded-tr-sm shadow-md' 
                : 'bg-gradient-to-br from-blue-50 to-indigo-50 text-gray-900 rounded-2xl rounded-tl-sm shadow-sm border border-blue-100'
            ]"
          >
            <div v-if="!message.order" :class="['px-4 py-3', message.isMine ? 'text-white' : 'text-gray-900']">
              <p class="whitespace-pre-wrap break-words">{{ message.text }}</p>
              <p 
                :class="['text-xs mt-1', message.isMine ? 'text-purple-200' : 'text-gray-500']"
              >
                {{ formatTime(message.createdAt) }}
              </p>
            </div>

            <div v-else class="p-3">
              <p :class="['mb-3 font-semibold', message.isMine ? 'text-white' : 'text-gray-900']">
                {{ message.text }}
              </p>
              <ChatOrderComponent 
                :order="message.order" 
                @updated="handleOrderUpdated"
              />
              <p 
                :class="['text-xs mt-2', message.isMine ? 'text-purple-200' : 'text-gray-500']"
              >
                {{ formatTime(message.createdAt) }}
              </p>
            </div>
          </div>
        </div>

        <div v-if="messages.length === 0" class="text-center py-8 text-gray-500">
          <p>Нет сообщений</p>
        </div>
      </div>

      <div class="glass-card-strongest p-4">
        <div class="flex gap-2">
          <textarea
            v-model="messageText"
            @keydown.enter.exact.prevent="sendMessage"
            placeholder="Введите сообщение..."
            rows="2"
            class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 resize-none"
            :disabled="sendingMessage"
          ></textarea>
          <button
            @click="sendMessage"
            :disabled="!messageText.trim() || sendingMessage"
            class="px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="sendingMessage" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <svg v-else class="w-5 h-5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { useChatStore } from '@/stores/chat'
import ChatOrderComponent from './ChatOrderComponent.vue'

const props = defineProps({
  chat: {
    type: Object,
    default: null
  },
  messages: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  sendingMessage: {
    type: Boolean,
    default: false
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  chatTitle: {
    type: String,
    default: 'Администратор'
  },
  chatSubtitle: {
    type: String,
    default: 'Поддержка'
  },
  emptyTitle: {
    type: String,
    default: 'Чат с администратором'
  },
  emptyMessage: {
    type: String,
    default: 'У вас пока нет активного чата'
  }
})

const emit = defineEmits(['send-message', 'order-updated'])

const chatStore = useChatStore()

const messageText = ref('')
const messagesContainer = ref(null)

const sendMessage = async () => {
  if (!messageText.value.trim() || !props.chat) return
  
  const text = messageText.value.trim()
  messageText.value = ''
  
  emit('send-message', text)
}

const handleOrderUpdated = (updatedOrder) => {
  emit('order-updated', updatedOrder)
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = (now - date) / (1000 * 60 * 60)
  
  if (diffInHours < 24) {
    return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
  } else if (diffInHours < 24 * 7) {
    return date.toLocaleDateString('ru-RU', { weekday: 'short', hour: '2-digit', minute: '2-digit' })
  } else {
    return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
  }
}

// Прокрутка вниз при изменении количества сообщений
watch(() => props.messages.length, async () => {
  await nextTick()
  scrollToBottom()
})

// Прокрутка вниз при смене чата
watch(() => props.chat?.id, async () => {
  await nextTick()
  scrollToBottom()
})

// Прокрутка вниз при первоначальной загрузке
onMounted(async () => {
  await nextTick()
  scrollToBottom()
})

defineExpose({
  scrollToBottom
})
</script>

<style scoped>
textarea {
  scrollbar-width: thin;
  scrollbar-color: #e5e7eb transparent;
}

textarea::-webkit-scrollbar {
  width: 6px;
}

textarea::-webkit-scrollbar-track {
  background: transparent;
}

textarea::-webkit-scrollbar-thumb {
  background-color: #e5e7eb;
  border-radius: 3px;
}
</style>
