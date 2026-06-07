<template>
  <Teleport to="body">
    <Transition name="fade-backdrop">
      <div
        v-if="visible"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
        @click.self="closeModal"
      >
        <Transition name="scale-modal">
          <div
            v-if="visible && photos.length > 0"
            class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col"
          >
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
              <h2 class="text-2xl font-bold text-gray-900">{{ title }}</h2>
              <button
                @click="closeModal"
                class="text-gray-500 hover:text-gray-700 transition-colors"
              >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
              <!-- Главное изображение -->
              <div class="bg-gray-50 rounded-2xl p-8 mb-6 flex items-center justify-center min-h-[400px]">
                <img
                  :src="photos[currentIndex]"
                  :alt="`${title} - фото ${currentIndex + 1}`"
                  class="max-w-full max-h-[500px] object-contain rounded-lg"
                />
              </div>

              <!-- Навигация между фото -->
              <div v-if="photos.length > 1" class="flex items-center justify-center gap-4 mb-6">
                <button
                  @click="previousPhoto"
                  class="p-3 bg-purple-600 text-white rounded-full hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="currentIndex === 0"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </button>

                <span class="text-lg font-semibold text-gray-700">
                  {{ currentIndex + 1 }} / {{ photos.length }}
                </span>

                <button
                  @click="nextPhoto"
                  class="p-3 bg-purple-600 text-white rounded-full hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="currentIndex === photos.length - 1"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>

              <!-- Миниатюры -->
              <div v-if="photos.length > 1" class="grid grid-cols-4 md:grid-cols-6 gap-3">
                <div
                  v-for="(photo, index) in photos"
                  :key="index"
                  @click="currentIndex = index"
                  class="cursor-pointer rounded-lg overflow-hidden border-4 transition-all hover:border-purple-400"
                  :class="currentIndex === index ? 'border-purple-600' : 'border-gray-200'"
                >
                  <img
                    :src="photo"
                    :alt="`Миниатюра ${index + 1}`"
                    class="w-full h-20 object-cover"
                  />
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    required: true
  },
  photos: {
    type: Array,
    default: () => []
  },
  title: {
    type: String,
    default: 'Галерея фотографий'
  },
  initialIndex: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['close'])

const currentIndex = ref(0)

const closeModal = () => {
  emit('close')
}

const nextPhoto = () => {
  if (currentIndex.value < props.photos.length - 1) {
    currentIndex.value++
  }
}

const previousPhoto = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
  }
}

// Обработка клавиш клавиатуры
const handleKeydown = (event) => {
  if (!props.visible) return
  
  if (event.key === 'ArrowRight') {
    nextPhoto()
  } else if (event.key === 'ArrowLeft') {
    previousPhoto()
  } else if (event.key === 'Escape') {
    closeModal()
  }
}

watch(() => props.visible, (newVal) => {
  if (newVal) {
    currentIndex.value = props.initialIndex
    document.body.style.overflow = 'hidden'
    window.addEventListener('keydown', handleKeydown)
  } else {
    document.body.style.overflow = ''
    window.removeEventListener('keydown', handleKeydown)
  }
})
</script>

<style scoped>
.fade-backdrop-enter-active,
.fade-backdrop-leave-active {
  transition: opacity 0.3s ease;
}

.fade-backdrop-enter-from,
.fade-backdrop-leave-to {
  opacity: 0;
}

.scale-modal-enter-active,
.scale-modal-leave-active {
  transition: all 0.3s ease;
}

.scale-modal-enter-from,
.scale-modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
