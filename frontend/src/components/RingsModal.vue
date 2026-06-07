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
            class="bg-white rounded-3xl shadow-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden flex flex-col"
          >
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
              <div>
                <h2 class="text-xl font-bold text-gray-900">{{ group?.nameRu || group?.typeCode }}</h2>
                <p class="text-gray-600 text-sm mt-0.5">{{ group?.typeCode }}</p>
              </div>
              <button
                @click="closeModal"
                class="text-gray-500 hover:text-gray-700 transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div v-if="group?.dimensionsPhotoUrl" class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-center">
              <img
                :src="group.dimensionsPhotoUrl"
                alt="Размерная схема"
                class="max-w-2xl max-h-56 rounded-xl shadow-lg cursor-pointer hover:shadow-xl transition-shadow"
                @click="openImageModal(group.dimensionsPhotoUrl, 'Размерная схема')"
              />
            </div>

            <div class="flex-1 flex flex-col overflow-hidden">
              <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-purple-600"></div>
                <p class="mt-4 text-gray-600">Загрузка колец...</p>
              </div>

              <div v-else-if="rings.length === 0" class="text-center py-12">
                <p class="text-gray-600 text-xl">Кольца не найдены</p>
              </div>

              <div v-else class="flex-1 flex flex-col overflow-hidden">
                <div class="">
                  <table class="w-full bg-white table-fixed">
                    <thead class="bg-gray-100">
                      <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 border-b-2 border-gray-200 w-32">Номер</th>
                        <th
                          v-for="(name, index) in columnNames"
                          :key="index"
                          class="px-6 py-4 text-left text-sm font-bold text-gray-900 border-b-2 border-gray-200 w-24"
                        >
                          {{ name }}
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 border-b-2 border-gray-200 w-28">Цена</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 border-b-2 border-gray-200 w-28">В наличии</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 border-b-2 border-gray-200 w-24">Фото</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 border-b-2 border-gray-200 w-32">Действия</th>
                      </tr>
                    </thead>
                  </table>
                </div>
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                  <table class="w-full bg-white table-fixed">
                    <colgroup>
                      <col class="w-32" />
                      <col v-for="(name, index) in columnNames" :key="index" class="w-24" />
                      <col class="w-28" />
                      <col class="w-28" />
                      <col class="w-24" />
                      <col class="w-32" />
                    </colgroup>
                    <tbody>
                      <tr
                        v-for="ring in rings"
                        :key="ring.id"
                        class="border-t border-gray-200 hover:bg-gray-50 transition-colors"
                      >
                        <td class="px-6 py-4">
                          <span class="font-semibold text-gray-900">{{ ring.partNumber }}</span>
                        </td>
                        <td
                          v-for="(name, index) in columnNames"
                          :key="index"
                          class="px-6 py-4 text-gray-700"
                        >
                          {{ ring.dimensions[index] || '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-900 font-semibold">
                          {{ ring.price ? `${ring.price} ₽` : '—' }}
                        </td>
                        <td class="px-6 py-4">
                          <span class="font-semibold" :class="ring.inStock > 0 ? 'text-green-600' : 'text-gray-500'">
                            {{ ring.inStock > 0 ? ring.inStock : '—' }}
                          </span>
                        </td>
                        <td class="px-6 py-4">
                          <img
                            v-if="ring.photoUrl"
                            :src="ring.photoUrl"
                            :alt="ring.partNumber"
                            class="w-16 h-16 object-contain rounded-lg cursor-pointer hover:shadow-lg transition-shadow"
                            @click="openImageModal(ring.photoUrl, ring.partNumber)"
                          />
                          <span v-else class="text-gray-400">—</span>
                        </td>
                        <td class="px-6 py-4">
                          <button
                            @click="addToCart(ring)"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold whitespace-nowrap"
                          >
                            В корзину
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>

  <ImageModal
    :visible="imageModalVisible"
    :imageUrl="selectedImage"
    :alt="selectedImageAlt"
    @close="closeImageModal"
  />
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import apiClient from '@/config/axios'
import ImageModal from './ImageModal.vue'

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

const emit = defineEmits(['close'])

const rings = ref([])
const loading = ref(false)
const imageModalVisible = ref(false)
const selectedImage = ref('')
const selectedImageAlt = ref('')

const columnNames = computed(() => {
  if (!props.group?.columnNames) return []
  
  const names = []
  const columnMap = props.group.columnNames
  
  let i = 1
  while (columnMap[i.toString()]) {
    names.push(columnMap[i.toString()])
    i++
  }
  
  return names
})

const closeModal = () => {
  emit('close')
}

const openImageModal = (url, alt) => {
  selectedImage.value = url
  selectedImageAlt.value = alt
  imageModalVisible.value = true
}

const closeImageModal = () => {
  imageModalVisible.value = false
}

const addToCart = (ring) => {
  console.log('Добавление в корзину:', ring)
  // TODO: Реализовать добавление в корзину
}

const fetchRings = async () => {
  if (!props.group?.id) return
  
  loading.value = true
  try {
    const response = await apiClient.get('/api/catalog/rings', {
      params: {
        groupId: props.group.id,
        inStockOnly: true
      }
    })
    rings.value = response.data || []
  } catch (error) {
    console.error('Ошибка загрузки колец:', error)
  } finally {
    loading.value = false
  }
}

watch(() => props.visible, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden'
    fetchRings()
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
