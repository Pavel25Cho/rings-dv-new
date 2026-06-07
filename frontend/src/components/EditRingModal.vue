<template>
  <Teleport to="body">
    <Transition name="fade-backdrop">
      <div
        v-if="visible"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="closeModal"
      >
        <Transition name="scale-modal">
          <div
            v-if="visible"
            class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col"
          >
          <div class="flex justify-between items-center p-8 border-b border-gray-200">
            <h2 class="text-3xl font-bold text-gray-900">Редактирование кольца</h2>
            <button
              @click="closeModal"
              class="text-gray-500 hover:text-gray-700 transition-colors"
            >
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="overflow-y-auto p-8">
            <form @submit.prevent="saveRing" class="space-y-6">
              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Номер (Артикул)</label>
                <input
                  v-model="formData.partNumber"
                  type="text"
                  class="input w-full"
                  placeholder="Введите номер"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Размеры (JSON массив)</label>
                <textarea
                  v-model="dimensionsString"
                  rows="4"
                  class="input w-full font-mono text-sm"
                  placeholder='["10.5", "12", "8.2"]'
                ></textarea>
                <p v-if="dimensionsError" class="mt-2 text-sm text-red-600">{{ dimensionsError }}</p>
                <p class="mt-2 text-sm text-gray-600">Введите размеры в виде JSON массива строк или чисел</p>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Цена (₽)</label>
                <input
                  v-model="formData.price"
                  type="number"
                  step="0.01"
                  class="input w-full"
                  placeholder="0.00"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Количество в наличии</label>
                <input
                  v-model.number="formData.inStock"
                  type="number"
                  min="0"
                  class="input w-full"
                  placeholder="0"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Фотографии кольца</label>
                <div class="space-y-3">
                  <input
                    type="file"
                    accept="image/*"
                    multiple
                    @change="handlePhotoUpload"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                  />
                  
                  <!-- Галерея фотографий -->
                  <div v-if="formData.photos && formData.photos.length > 0" class="grid grid-cols-3 gap-3">
                    <div
                      v-for="(photo, index) in formData.photos"
                      :key="index"
                      class="relative group"
                    >
                      <img
                        :src="photo"
                        alt="Фото кольца"
                        class="w-full h-32 object-contain rounded-lg border-2 border-gray-200"
                      />
                      <button
                        type="button"
                        @click="removePhoto(index)"
                        class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                        title="Удалить фото"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <p v-if="formData.photos && formData.photos.length > 0" class="text-sm text-gray-600">
                    Загружено фотографий: {{ formData.photos.length }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <input
                  v-model="formData.isHidden"
                  type="checkbox"
                  id="isHidden"
                  class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500"
                />
                <label for="isHidden" class="text-sm font-bold text-gray-900">Скрыть кольцо</label>
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  type="submit"
                  :disabled="saving || uploading"
                  class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold disabled:opacity-50"
                >
                  {{ uploading ? 'Загрузка фото...' : (saving ? 'Сохранение...' : 'Сохранить') }}
                </button>
                <button
                  type="button"
                  @click="closeModal"
                  :disabled="saving || uploading"
                  class="px-6 py-3 bg-gray-300 text-gray-900 rounded-xl hover:bg-gray-400 transition-colors font-semibold disabled:opacity-50"
                >
                  Отмена
                </button>
              </div>
            </form>
          </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import apiClient from '@/config/axios'

const props = defineProps({
  visible: {
    type: Boolean,
    required: true
  },
  ring: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const formData = ref({
  partNumber: '',
  dimensions: [],
  price: '',
  inStock: 0,
  photos: [],
  isHidden: false
})

const dimensionsString = ref('')
const dimensionsError = ref('')
const saving = ref(false)
const uploading = ref(false)

const closeModal = () => {
  emit('close')
}

const uploadImage = async (file) => {
  const formDataUpload = new FormData()
  formDataUpload.append('image', file)
  
  try {
    uploading.value = true
    const response = await apiClient.post('/api/admin/upload-image', formDataUpload, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data.url
  } catch (error) {
    console.error('Ошибка загрузки изображения:', error)
    alert('Ошибка при загрузке изображения')
    return null
  } finally {
    uploading.value = false
  }
}

const handlePhotoUpload = async (event) => {
  const files = Array.from(event.target.files)
  if (!files.length) return
  
  uploading.value = true
  
  for (const file of files) {
    const url = await uploadImage(file)
    if (url) {
      formData.value.photos.push(url)
    }
  }
  
  uploading.value = false
  event.target.value = ''
}

const removePhoto = (index) => {
  formData.value.photos.splice(index, 1)
}

const saveRing = async () => {
  if (!props.ring?.id) return

  // Валидация JSON для dimensions
  if (dimensionsString.value.trim()) {
    try {
      const parsed = JSON.parse(dimensionsString.value)
      if (!Array.isArray(parsed)) {
        dimensionsError.value = 'Размеры должны быть массивом'
        return
      }
      formData.value.dimensions = parsed
      dimensionsError.value = ''
    } catch (e) {
      dimensionsError.value = 'Неверный формат JSON'
      return
    }
  } else {
    formData.value.dimensions = []
  }

  saving.value = true
  try {
    await apiClient.patch(`/api/admin/rings/${props.ring.id}`, formData.value)
    emit('saved')
  } catch (error) {
    console.error('Ошибка сохранения кольца:', error)
    alert('Ошибка при сохранении кольца')
  } finally {
    saving.value = false
  }
}

watch(() => props.visible, (newVal) => {
  if (newVal && props.ring) {
    formData.value = {
      partNumber: props.ring.partNumber || '',
      dimensions: props.ring.dimensions || [],
      price: props.ring.price || '',
      inStock: props.ring.inStock || 0,
      photos: props.ring.photos || [],
      isHidden: props.ring.isHidden || false
    }
    dimensionsString.value = props.ring.dimensions ? JSON.stringify(props.ring.dimensions, null, 2) : '[]'
    dimensionsError.value = ''
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
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
  transform: scale(0.9);
}
</style>
