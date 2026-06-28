<template>
  <div class="flex flex-col h-full">
    <ImagePreviewModal
      :is-open="previewModalOpen"
      :attachment="selectedAttachment"
      @close="closePreviewModal"
    />
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
        <div v-if="hasMoreMessages" class="flex justify-center mb-4">
          <button
            @click="loadOlderMessages"
            :disabled="loadingOlderMessages"
            class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <svg v-if="loadingOlderMessages" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            </svg>
            <span>{{ loadingOlderMessages ? 'Загрузка...' : 'Загрузить старые сообщения' }}</span>
          </button>
        </div>
        
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
                : isAnotherAdmin(message)
                ? 'bg-gradient-to-br from-emerald-50 to-teal-50 text-gray-900 rounded-2xl rounded-tl-sm shadow-sm border-2 border-emerald-400'
                : 'bg-gradient-to-br from-blue-50 to-indigo-50 text-gray-900 rounded-2xl rounded-tl-sm shadow-sm border border-blue-100'
            ]"
          >
            <div v-if="!message.order" :class="['px-4 py-3', message.isMine ? 'text-white' : 'text-gray-900']">
              <p v-if="message.text" class="whitespace-pre-wrap break-words">{{ message.text }}</p>
              
              <div v-if="message.attachments && message.attachments.length > 0" :class="['mt-2 space-y-2', message.text ? 'pt-2 border-t' : '', message.isMine ? 'border-purple-400' : 'border-gray-200']">
                <div
                  v-for="attachment in message.attachments"
                  :key="attachment.id"
                  class="flex items-center gap-2 p-2 rounded-lg bg-white bg-opacity-10 hover:bg-opacity-20 transition-all cursor-pointer group"
                  @click="downloadAttachment(attachment)"
                >
                  <div class="flex-shrink-0">
                    <div v-if="attachment.isImage" class="w-16 h-16 relative group/image">
                      <img
                        v-if="imageUrls[attachment.id]"
                        :src="imageUrls[attachment.id]"
                        :alt="attachment.originalFilename"
                        class="w-full h-full object-cover rounded"
                        loading="lazy"
                      />
                      <div v-else class="w-full h-full bg-white bg-opacity-20 rounded flex items-center justify-center">
                        <svg class="w-6 h-6 animate-spin" :class="message.isMine ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                      </div>
                      <div class="absolute inset-0 bg-black bg-opacity-0 group-hover/image:bg-opacity-30 transition-all rounded flex items-center justify-center">
                        <svg class="w-6 h-6 text-white opacity-0 group-hover/image:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                        </svg>
                      </div>
                    </div>
                    <div v-else class="w-10 h-10 bg-white bg-opacity-20 rounded flex items-center justify-center">
                      <svg class="w-6 h-6" :class="message.isMine ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ attachment.originalFilename }}</p>
                    <p class="text-xs opacity-75">{{ attachment.fileSizeFormatted }}</p>
                  </div>
                  <svg v-if="attachment.isImage" class="w-5 h-5 opacity-0 group-hover:opacity-100 transition-opacity" :class="message.isMine ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5 opacity-0 group-hover:opacity-100 transition-opacity" :class="message.isMine ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                </div>
              </div>
              
              <p 
                :class="['text-xs mt-2', message.isMine ? 'text-purple-200' : isAnotherAdmin(message) ? 'text-emerald-600' : 'text-gray-500']"
              >
                {{ formatTime(message.createdAt) }}
                <span v-if="isAnotherAdmin(message) && authStore.isAdmin" class="ml-2 font-medium">
                  • {{ message.sender.email }}
                </span>
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
                :class="['text-xs mt-2', message.isMine ? 'text-purple-200' : isAnotherAdmin(message) ? 'text-emerald-600' : 'text-gray-500']"
              >
                {{ formatTime(message.createdAt) }}
                <span v-if="isAnotherAdmin(message)" class="ml-2 font-medium">
                  • {{ message.sender.email }}
                </span>
              </p>
            </div>
          </div>
        </div>

        <div v-if="messages.length === 0" class="text-center py-8 text-gray-500">
          <p>Нет сообщений</p>
        </div>
      </div>

      <div class="glass-card-strongest p-4">
        <div v-if="selectedFile" class="mb-3 p-3 bg-purple-50 rounded-lg flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg v-if="isImageFile(selectedFile)" class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <svg v-else class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ selectedFile.name }}</p>
              <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
            </div>
          </div>
          <button
            @click="removeSelectedFile"
            class="p-1 hover:bg-purple-200 rounded-full transition-colors"
            type="button"
          >
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="flex gap-2">
          <input
            ref="fileInput"
            type="file"
            @change="handleFileSelect"
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
            class="hidden"
          />
          <button
            @click="$refs.fileInput.click()"
            :disabled="sendingMessage || uploadingFile"
            class="px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            type="button"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
          </button>
          <textarea
            v-model="messageText"
            @keydown.enter.exact.prevent="sendMessage"
            placeholder="Введите сообщение..."
            rows="2"
            class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 resize-none"
            :disabled="sendingMessage || uploadingFile"
          ></textarea>
          <button
            @click="sendMessage"
            :disabled="(!messageText.trim() && !selectedFile) || sendingMessage || uploadingFile"
            class="px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="sendingMessage || uploadingFile" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/config/axios'
import ChatOrderComponent from './ChatOrderComponent.vue'
import ImagePreviewModal from './ImagePreviewModal.vue'

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
  },
  hasMoreMessages: {
    type: Boolean,
    default: false
  },
  loadingOlderMessages: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['send-message', 'order-updated', 'load-older-messages', 'file-uploaded'])

const chatStore = useChatStore()
const authStore = useAuthStore()

const messageText = ref('')
const messagesContainer = ref(null)
const previousScrollHeight = ref(0)
const selectedFile = ref(null)
const fileInput = ref(null)
const uploadingFile = ref(false)
const imageUrls = ref({})
const previewModalOpen = ref(false)
const selectedAttachment = ref(null)

const sendMessage = async () => {
  if ((!messageText.value.trim() && !selectedFile.value) || !props.chat) return
  
  if (selectedFile.value) {
    await uploadFile()
  } else {
    const text = messageText.value.trim()
    messageText.value = ''
    emit('send-message', text)
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  const maxSize = 10 * 1024 * 1024
  if (file.size > maxSize) {
    alert('Файл слишком большой. Максимальный размер: 10 МБ')
    return
  }

  const allowedTypes = [
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ]

  if (!allowedTypes.includes(file.type)) {
    alert('Недопустимый тип файла. Разрешены: изображения, PDF, Word, Excel')
    return
  }

  selectedFile.value = file
  event.target.value = ''
}

const removeSelectedFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const uploadFile = async () => {
  if (!selectedFile.value || !props.chat) return

  uploadingFile.value = true

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    
    if (messageText.value.trim()) {
      formData.append('text', messageText.value.trim())
    }

    const response = await apiClient.post(`/api/chat/upload-file/${props.chat.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      messageText.value = ''
      selectedFile.value = null
      if (fileInput.value) {
        fileInput.value.value = ''
      }
      
      emit('file-uploaded', response.data.message)
    } else {
      alert(response.data.message || 'Ошибка при загрузке файла')
    }
  } catch (error) {
    console.error('Upload error:', error)
    const message = error.response?.data?.message || 'Ошибка при загрузке файла'
    alert(message)
  } finally {
    uploadingFile.value = false
  }
}

const downloadAttachment = async (attachment) => {
  // Если это изображение - открываем в модальном окне
  if (attachment.isImage) {
    openPreviewModal(attachment)
    return
  }

  // Для документов - сразу скачиваем
  try {
    const response = await apiClient.get(attachment.downloadUrl, {
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: attachment.mimeType })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = attachment.originalFilename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Download error:', error)
    alert('Ошибка при скачивании файла')
  }
}

const openPreviewModal = (attachment) => {
  selectedAttachment.value = attachment
  previewModalOpen.value = true
}

const closePreviewModal = () => {
  previewModalOpen.value = false
  selectedAttachment.value = null
}

const loadImageUrl = async (attachment) => {
  if (!attachment.isImage || imageUrls.value[attachment.id]) {
    return
  }

  try {
    const response = await apiClient.get(attachment.downloadUrl, {
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: attachment.mimeType })
    const url = window.URL.createObjectURL(blob)
    imageUrls.value[attachment.id] = url
  } catch (error) {
    console.error('Error loading image:', error)
  }
}

const loadImagesForMessages = () => {
  props.messages.forEach(message => {
    if (message.attachments) {
      message.attachments.forEach(attachment => {
        if (attachment.isImage && !imageUrls.value[attachment.id]) {
          loadImageUrl(attachment)
        }
      })
    }
  })
}

const isImageFile = (file) => {
  return file && file.type && file.type.startsWith('image/')
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Б'
  const k = 1024
  const sizes = ['Б', 'КБ', 'МБ', 'ГБ']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const loadOlderMessages = async () => {
  if (!messagesContainer.value) return
  
  // Сохраняем текущую высоту скролла перед загрузкой
  previousScrollHeight.value = messagesContainer.value.scrollHeight
  
  emit('load-older-messages')
}

const handleOrderUpdated = (updatedOrder) => {
  emit('order-updated', updatedOrder)
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const maintainScrollPosition = () => {
  if (messagesContainer.value && previousScrollHeight.value > 0) {
    // Вычисляем разницу в высоте и корректируем позицию скролла
    const newScrollHeight = messagesContainer.value.scrollHeight
    const heightDifference = newScrollHeight - previousScrollHeight.value
    messagesContainer.value.scrollTop += heightDifference
    previousScrollHeight.value = 0
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

const isAnotherAdmin = (message) => {
  return !message.isMine && message.sender?.isAdmin === true && authStore.isAdmin
}

// Прокрутка вниз при изменении количества сообщений (только если не загружаем старые)
watch(() => props.messages.length, async (newLength, oldLength) => {
  await nextTick()
  
  // Загружаем изображения для новых сообщений
  loadImagesForMessages()
  
  // Если загружали старые сообщения (добавили в начало) - сохраняем позицию
  if (props.loadingOlderMessages || previousScrollHeight.value > 0) {
    maintainScrollPosition()
  } else if (newLength > oldLength) {
    // Новое сообщение добавлено в конец - прокручиваем вниз
    scrollToBottom()
  }
})

// Загружаем изображения при изменении сообщений
watch(() => props.messages, () => {
  loadImagesForMessages()
}, { deep: true })

// Прокрутка вниз при смене чата
watch(() => props.chat?.id, async () => {
  await nextTick()
  scrollToBottom()
})

// Прокрутка вниз при первоначальной загрузке
onMounted(async () => {
  await nextTick()
  loadImagesForMessages()
  scrollToBottom()
})

// Очистка blob URLs при размонтировании компонента
onUnmounted(() => {
  Object.values(imageUrls.value).forEach(url => {
    if (url) {
      window.URL.revokeObjectURL(url)
    }
  })
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
