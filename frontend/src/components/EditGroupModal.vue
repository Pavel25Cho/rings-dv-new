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
            <h2 class="text-3xl font-bold text-gray-900">Редактирование группы</h2>
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
            <form @submit.prevent="saveGroup" class="space-y-6">
              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Название (RU)</label>
                <input
                  v-model="formData.nameRu"
                  type="text"
                  class="input w-full"
                  placeholder="Введите русское название"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Название (EN)</label>
                <input
                  v-model="formData.nameEn"
                  type="text"
                  class="input w-full"
                  placeholder="Введите английское название"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Тип</label>
                <input
                  v-model="formData.typeCode"
                  type="text"
                  class="input w-full"
                  placeholder="Введите тип"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Бренд</label>
                <input
                  v-model="formData.brand"
                  type="text"
                  class="input w-full"
                  placeholder="Введите бренд"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Материал (EN)</label>
                <input
                  v-model="formData.materialEn"
                  type="text"
                  class="input w-full"
                  placeholder="Введите материал на английском"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Материал (RU)</label>
                <input
                  v-model="formData.materialRu"
                  type="text"
                  class="input w-full"
                  placeholder="Введите материал на русском"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Фото профиля</label>
                <div class="space-y-3">
                  <input
                    type="file"
                    accept="image/*"
                    @change="handlePhotoUpload"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                  />
                  <img
                    v-if="formData.photoUrl"
                    :src="formData.photoUrl"
                    alt="Предпросмотр"
                    class="mt-2 w-32 h-32 object-contain rounded-lg border-2 border-gray-200"
                  />
                  <button
                    v-if="formData.photoUrl"
                    type="button"
                    @click="formData.photoUrl = ''"
                    class="text-sm text-red-600 hover:text-red-800"
                  >
                    Удалить фото
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Фото размерной схемы</label>
                <div class="space-y-3">
                  <input
                    type="file"
                    accept="image/*"
                    @change="handleDimensionsPhotoUpload"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                  />
                  <img
                    v-if="formData.dimensionsPhotoUrl"
                    :src="formData.dimensionsPhotoUrl"
                    alt="Предпросмотр размеров"
                    class="mt-2 w-full max-w-md object-contain rounded-lg border-2 border-gray-200"
                  />
                  <button
                    v-if="formData.dimensionsPhotoUrl"
                    type="button"
                    @click="formData.dimensionsPhotoUrl = ''"
                    class="text-sm text-red-600 hover:text-red-800"
                  >
                    Удалить фото
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Названия колонок (JSON)</label>
                <textarea
                  v-model="columnNamesString"
                  rows="4"
                  class="input w-full font-mono text-sm"
                  placeholder='{"1": "D1", "2": "D2", "3": "d"}'
                ></textarea>
                <p v-if="columnNamesError" class="mt-2 text-sm text-red-600">{{ columnNamesError }}</p>
              </div>

              <div class="flex items-center gap-3">
                <input
                  v-model="formData.isHidden"
                  type="checkbox"
                  id="isHidden"
                  class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500"
                />
                <label for="isHidden" class="text-sm font-bold text-gray-900">Скрыть группу</label>
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
import { ref, watch, computed } from 'vue'
import apiClient from '@/config/axios'

const props = defineProps({
  visible: {
    type: Boolean,
    required: true
  },
  group: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const formData = ref({
  nameRu: '',
  nameEn: '',
  typeCode: '',
  brand: '',
  materialEn: '',
  materialRu: '',
  photoUrl: '',
  dimensionsPhotoUrl: '',
  columnNames: null,
  isHidden: false
})

const columnNamesString = ref('')
const columnNamesError = ref('')
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
  const file = event.target.files[0]
  if (!file) return
  
  const url = await uploadImage(file)
  if (url) {
    formData.value.photoUrl = url
  }
}

const handleDimensionsPhotoUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  const url = await uploadImage(file)
  if (url) {
    formData.value.dimensionsPhotoUrl = url
  }
}

const saveGroup = async () => {
  if (!props.group?.id) return

  // Валидация JSON для columnNames
  if (columnNamesString.value.trim()) {
    try {
      formData.value.columnNames = JSON.parse(columnNamesString.value)
      columnNamesError.value = ''
    } catch (e) {
      columnNamesError.value = 'Неверный формат JSON'
      return
    }
  } else {
    formData.value.columnNames = null
  }

  saving.value = true
  try {
    await apiClient.patch(`/api/admin/groups/${props.group.id}`, formData.value)
    emit('saved')
  } catch (error) {
    console.error('Ошибка сохранения группы:', error)
    alert('Ошибка при сохранении группы')
  } finally {
    saving.value = false
  }
}

watch(() => props.visible, (newVal) => {
  if (newVal && props.group) {
    formData.value = {
      nameRu: props.group.nameRu || '',
      nameEn: props.group.nameEn || '',
      typeCode: props.group.typeCode || '',
      brand: props.group.brand || '',
      materialEn: props.group.materialEn || '',
      materialRu: props.group.materialRu || '',
      photoUrl: props.group.photoUrl || '',
      dimensionsPhotoUrl: props.group.dimensionsPhotoUrl || '',
      columnNames: props.group.columnNames || null,
      isHidden: props.group.isHidden || false
    }
    columnNamesString.value = props.group.columnNames ? JSON.stringify(props.group.columnNames, null, 2) : ''
    columnNamesError.value = ''
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
