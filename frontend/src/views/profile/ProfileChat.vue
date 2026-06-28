<template>
  <div class="h-full">
    <ChatWindow
      ref="chatWindow"
      :chat="chatStore.myChat"
      :messages="messages"
      :loading="loading"
      :sending-message="sendingMessage"
      :show-header="true"
      chat-title="Администратор"
      chat-subtitle="Поддержка"
      empty-title="Чат с администратором"
      empty-message="У вас пока нет активного чата. Чат будет создан автоматически при оформлении заказа."
      @send-message="handleSendMessage"
      @order-updated="handleOrderUpdated"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import ChatWindow from '@/components/ChatWindow.vue'

const chatStore = useChatStore()
const authStore = useAuthStore()

const chatWindow = ref(null)
const loading = ref(true)
const pollingInterval = ref(null)

const messages = computed(() => chatStore.messages)
const sendingMessage = computed(() => chatStore.sendingMessage)

const loadChat = async () => {
  loading.value = true
  try {
    await chatStore.fetchMyChat()
    
    if (chatStore.myChat) {
      await chatStore.fetchMessages(chatStore.myChat.id)
      await chatStore.markAsRead(chatStore.myChat.id)
      
      // Ждем отрисовки и прокручиваем вниз
      await nextTick()
      setTimeout(() => {
        chatWindow.value?.scrollToBottom()
      }, 100)
    }
  } catch (error) {
    console.error('Ошибка загрузки чата:', error)
  } finally {
    loading.value = false
  }
}

const handleSendMessage = async (text) => {
  if (!chatStore.myChat) return
  
  const result = await chatStore.sendMessage(chatStore.myChat.id, text)
  
  if (!result.success) {
    alert(result.message || 'Не удалось отправить сообщение')
  }
}

const handleOrderUpdated = (updatedOrder) => {
  // Обновляем заказ в сообщениях без перезагрузки
  chatStore.updateOrderInMessages(updatedOrder)
}

const startPolling = () => {
  stopPolling()
  
  pollingInterval.value = setInterval(async () => {
    if (chatStore.myChat) {
      const oldLength = messages.value.length
      await chatStore.fetchMessages(chatStore.myChat.id)
      
      if (messages.value.length > oldLength) {
        await nextTick()
        chatWindow.value?.scrollToBottom()
      }
      
      await chatStore.fetchUnreadCount()
    }
  }, 15000)
}

const stopPolling = () => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
    pollingInterval.value = null
  }
}

onMounted(() => {
  loadChat()
  startPolling()
})

onUnmounted(() => {
  stopPolling()
})
</script>
