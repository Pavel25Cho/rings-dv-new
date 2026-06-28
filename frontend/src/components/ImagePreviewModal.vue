<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75"
        @click.self="close"
      >
        <div class="relative max-w-5xl max-h-[90vh] w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
          <!-- Header -->
          <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-600 to-pink-600">
            <h3 class="text-lg font-bold text-white truncate flex-1 pr-4">
              {{ attachment?.originalFilename }}
            </h3>
            <button
              @click="close"
              class="p-2 hover:bg-white/20 rounded-full transition-colors flex-shrink-0"
              type="button"
            >
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Image container -->
          <div class="flex items-center justify-center p-8 bg-gray-50 max-h-[calc(90vh-150px)] overflow-auto">
            <img
              v-if="imageUrl"
              :src="imageUrl"
              :alt="attachment?.originalFilename"
              class="max-w-full max-h-full object-contain rounded-lg shadow-lg"
            />
            <div v-else class="flex flex-col items-center justify-center py-12">
              <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-purple-600 mb-4"></div>
              <p class="text-gray-600">Загрузка изображения...</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between p-4 bg-white border-t border-gray-200">
            <div class="text-sm text-gray-600">
              <p class="font-medium">{{ attachment?.fileSizeFormatted }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ attachment?.mimeType }}</p>
            </div>
            <button
              @click="downloadImage"
              class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all font-semibold shadow-lg hover:shadow-xl flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Скачать
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import apiClient from '@/config/axios'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  attachment: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const imageUrl = ref(null)
const loading = ref(false)

const close = () => {
  emit('close')
}

const loadImage = async () => {
  if (!props.attachment || !props.isOpen) {
    return
  }

  loading.value = true
  imageUrl.value = null

  try {
    const response = await apiClient.get(props.attachment.downloadUrl, {
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: props.attachment.mimeType })
    imageUrl.value = window.URL.createObjectURL(blob)
  } catch (error) {
    console.error('Error loading image:', error)
    alert('Ошибка при загрузке изображения')
    close()
  } finally {
    loading.value = false
  }
}

const downloadImage = async () => {
  if (!imageUrl.value || !props.attachment) {
    return
  }

  try {
    const link = document.createElement('a')
    link.href = imageUrl.value
    link.download = props.attachment.originalFilename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (error) {
    console.error('Download error:', error)
    alert('Ошибка при скачивании файла')
  }
}

watch(() => props.isOpen, (newValue) => {
  if (newValue && props.attachment) {
    loadImage()
  } else if (!newValue && imageUrl.value) {
    window.URL.revokeObjectURL(imageUrl.value)
    imageUrl.value = null
  }
})

// Обработка нажатия Escape
watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    const handleEscape = (e) => {
      if (e.key === 'Escape') {
        close()
      }
    }
    window.addEventListener('keydown', handleEscape)
    return () => window.removeEventListener('keydown', handleEscape)
  }
})
</script>
