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
            <h2 class="text-3xl font-bold text-gray-900">{{ props.ring ? 'Редактирование кольца' : 'Создание кольца' }}</h2>
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
                <label class="block text-sm font-bold text-gray-900 mb-2">Группа</label>
                <div v-if="!props.ring" class="relative">
                  <input
                    v-model="groupSearchQuery"
                    @focus="showGroupDropdown = true"
                    @input="showGroupDropdown = true"
                    type="text"
                    class="input w-full"
                    placeholder="Начните вводить название группы..."
                    required
                  />
                  <div
                    v-if="showGroupDropdown && filteredGroups.length > 0"
                    class="absolute z-30 mt-2 w-full bg-white rounded-xl shadow-lg border border-gray-200 max-h-60 overflow-y-auto"
                  >
                    <button
                      v-for="group in filteredGroups"
                      :key="group.id"
                      type="button"
                      @click="selectGroup(group)"
                      class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors border-b border-gray-100 last:border-b-0"
                    >
                      <div class="font-bold text-gray-900">{{ group.nameRu }}</div>
                      <div class="text-sm text-gray-600">{{ group.typeCode }} • {{ group.brand || 'Без бренда' }}</div>
                    </button>
                  </div>
                </div>
                <div v-else class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                  <div class="font-bold text-gray-900">{{ currentGroupInfo?.nameRu || 'Группа не найдена' }}</div>
                  <div class="text-sm text-gray-600">{{ currentGroupInfo?.typeCode }} • {{ currentGroupInfo?.brand || 'Без бренда' }}</div>
                </div>
                <p v-if="selectedGroupName && !props.ring" class="mt-2 text-sm text-gray-600">
                  Выбрано: <span class="font-bold text-purple-600">{{ selectedGroupName }}</span>
                </p>
              </div>

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
                <label class="block text-sm font-bold text-gray-900 mb-2">Размеры</label>
                <div v-if="currentGroupColumnNames.length > 0" class="mb-3 p-3 bg-purple-50 rounded-lg border border-purple-200">
                  <div class="text-sm font-bold text-purple-900 mb-2">Названия колонок для этой группы:</div>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="(columnName, index) in currentGroupColumnNames"
                      :key="index"
                      class="px-3 py-1 bg-white rounded-lg text-sm font-semibold text-purple-700 border border-purple-300"
                    >
                      {{ columnName }}
                    </span>
                  </div>
                </div>
                <input
                  v-model="dimensionsInput"
                  type="text"
                  class="input w-full"
                  placeholder="Введите размеры через запятую: 10.5, 12, 8.2"
                />
                <p class="mt-2 text-sm text-gray-600">
                  Введите размеры через запятую в порядке колонок выше. Например: 10.5, 12, 8.2, 15
                </p>
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
                      class="relative group cursor-move"
                      draggable="true"
                      @dragstart="handleDragStart(index)"
                      @dragover.prevent="handleDragOver(index)"
                      @drop="handleDrop(index)"
                      @dragend="handleDragEnd"
                      :class="{ 'opacity-50': draggedIndex === index, 'ring-2 ring-purple-500': dragOverIndex === index }"
                    >
                      <img
                        :src="photo"
                        alt="Фото кольца"
                        class="w-full h-32 object-contain rounded-lg border-2 border-gray-200"
                      />
                      
                      <!-- Номер фотографии -->
                      <div class="absolute top-2 left-2 bg-purple-600 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                        {{ index + 1 }}
                      </div>
                      
                      <!-- Кнопки управления -->
                      <div class="absolute bottom-2 left-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                          v-if="index > 0"
                          type="button"
                          @click="movePhotoUp(index)"
                          class="p-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                          title="Переместить влево"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                          </svg>
                        </button>
                        <button
                          v-if="index < formData.photos.length - 1"
                          type="button"
                          @click="movePhotoDown(index)"
                          class="p-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                          title="Переместить вправо"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                          </svg>
                        </button>
                      </div>
                      
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
                    Загружено фотографий: {{ formData.photos.length }}. Используйте стрелки или перетаскивайте фото для изменения порядка.
                  </p>
                </div>
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  type="submit"
                  :disabled="saving || uploading || deleting"
                  class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold disabled:opacity-50"
                >
                  {{ uploading ? 'Загрузка фото...' : (saving ? 'Сохранение...' : 'Сохранить') }}
                </button>
                <button
                  v-if="props.ring?.id"
                  type="button"
                  @click="deleteRing"
                  :disabled="saving || uploading || deleting"
                  class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-semibold disabled:opacity-50"
                >
                  {{ deleting ? 'Удаление...' : 'Удалить' }}
                </button>
                <button
                  type="button"
                  @click="closeModal"
                  :disabled="saving || uploading || deleting"
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
import { ref, watch, computed } from 'vue'
import apiClient from '@/config/axios'

const props = defineProps({
  visible: {
    type: Boolean,
    required: true
  },
  ring: {
    type: Object,
    default: null
  },
  groups: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'saved', 'deleted'])

const formData = ref({
  ringGroup: '',
  partNumber: '',
  dimensions: [],
  price: '',
  inStock: 0,
  photos: []
})

const dimensionsInput = ref('')
const groupSearchQuery = ref('')
const showGroupDropdown = ref(false)
const selectedGroupName = ref('')
const saving = ref(false)
const uploading = ref(false)
const deleting = ref(false)
const draggedIndex = ref(null)
const dragOverIndex = ref(null)

const filteredGroups = computed(() => {
  if (!groupSearchQuery.value) {
    return props.groups || []
  }
  const query = groupSearchQuery.value.toLowerCase()
  return (props.groups || []).filter(group => 
    group.nameRu?.toLowerCase().includes(query) ||
    group.nameEn?.toLowerCase().includes(query) ||
    group.typeCode?.toLowerCase().includes(query) ||
    group.brand?.toLowerCase().includes(query)
  )
})

const currentGroupInfo = computed(() => {
  if (!formData.value.ringGroup || !props.groups) {
    return null
  }
  return props.groups.find(g => g.id === formData.value.ringGroup) || null
})

const currentGroupColumnNames = computed(() => {
  if (!currentGroupInfo.value || !currentGroupInfo.value.columnNames) {
    return []
  }
  
  // Поддержка как массива, так и объекта (старый формат)
  if (Array.isArray(currentGroupInfo.value.columnNames)) {
    return currentGroupInfo.value.columnNames
  } else if (typeof currentGroupInfo.value.columnNames === 'object') {
    return Object.values(currentGroupInfo.value.columnNames)
  }
  
  return []
})

const selectGroup = (group) => {
  formData.value.ringGroup = group.id
  groupSearchQuery.value = `${group.nameRu} (${group.typeCode})`
  selectedGroupName.value = `${group.nameRu} (${group.typeCode})`
  showGroupDropdown.value = false
}

const closeModal = () => {
  emit('close')
}

// Закрытие dropdown при клике вне элемента
const handleClickOutside = (event) => {
  if (showGroupDropdown.value) {
    const dropdown = event.target.closest('.relative')
    if (!dropdown) {
      showGroupDropdown.value = false
    }
  }
}

// Добавляем слушатель при монтировании
watch(() => props.visible, (newVal) => {
  if (newVal) {
    setTimeout(() => {
      document.addEventListener('click', handleClickOutside)
    }, 100)
  } else {
    document.removeEventListener('click', handleClickOutside)
  }
})

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

const movePhotoUp = (index) => {
  if (index > 0) {
    const photos = [...formData.value.photos]
    const temp = photos[index]
    photos[index] = photos[index - 1]
    photos[index - 1] = temp
    formData.value.photos = photos
  }
}

const movePhotoDown = (index) => {
  if (index < formData.value.photos.length - 1) {
    const photos = [...formData.value.photos]
    const temp = photos[index]
    photos[index] = photos[index + 1]
    photos[index + 1] = temp
    formData.value.photos = photos
  }
}

const handleDragStart = (index) => {
  draggedIndex.value = index
}

const handleDragOver = (index) => {
  dragOverIndex.value = index
}

const handleDrop = (index) => {
  if (draggedIndex.value !== null && draggedIndex.value !== index) {
    const photos = [...formData.value.photos]
    const draggedPhoto = photos[draggedIndex.value]
    photos.splice(draggedIndex.value, 1)
    photos.splice(index, 0, draggedPhoto)
    formData.value.photos = photos
  }
  draggedIndex.value = null
  dragOverIndex.value = null
}

const handleDragEnd = () => {
  draggedIndex.value = null
  dragOverIndex.value = null
}

const saveRing = async () => {
  // Парсинг размеров из строки через запятую
  let dimensions = []
  if (dimensionsInput.value.trim()) {
    dimensions = dimensionsInput.value
      .split(',')
      .map(d => d.trim())
      .filter(d => d !== '')
  }

  // Проверка группы при создании
  if (!props.ring && !formData.value.ringGroup) {
    alert('Пожалуйста, выберите группу')
    return
  }

  // Создаем объект данных для отправки
  const dataToSend = {
    ...formData.value,
    dimensions: Array.from(dimensions) // Принудительно создаем массив
  }

  saving.value = true
  try {
    if (props.ring?.id) {
      // Редактирование существующего кольца
      await apiClient.patch(`/api/admin/rings/${props.ring.id}`, dataToSend)
    } else {
      // Создание нового кольца
      await apiClient.post('/api/admin/rings', dataToSend)
    }
    emit('saved')
  } catch (error) {
    console.error('Ошибка сохранения кольца:', error)
    alert('Ошибка при сохранении кольца')
  } finally {
    saving.value = false
  }
}

const deleteRing = async () => {
  if (!props.ring?.id) return
  
  if (!confirm('Вы уверены, что хотите удалить это кольцо? Это действие нельзя отменить.')) {
    return
  }

  deleting.value = true
  try {
    await apiClient.delete(`/api/admin/rings/${props.ring.id}`)
    emit('deleted')
  } catch (error) {
    console.error('Ошибка удаления кольца:', error)
    alert('Ошибка при удалении кольца')
  } finally {
    deleting.value = false
  }
}

watch(() => props.visible, (newVal) => {
  if (newVal) {
    if (props.ring) {
      // Режим редактирования
      formData.value = {
        ringGroup: props.ring.ringGroup || '',
        partNumber: props.ring.partNumber || '',
        dimensions: props.ring.dimensions || [],
        price: props.ring.price || '',
        inStock: props.ring.inStock || 0,
        photos: props.ring.photos || []
      }
      dimensionsInput.value = props.ring.dimensions ? props.ring.dimensions.join(', ') : ''
    } else {
      // Режим создания - очищаем форму
      formData.value = {
        ringGroup: '',
        partNumber: '',
        dimensions: [],
        price: '',
        inStock: 0,
        photos: []
      }
      dimensionsInput.value = ''
      groupSearchQuery.value = ''
      selectedGroupName.value = ''
      showGroupDropdown.value = false
    }
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
