<template>
  <div>
    <div class="px-4 md:px-8 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="glass-card-strong rounded-3xl p-10 mb-8">
          <div class="flex justify-between items-center mb-6">
            <div>
              <h1 class="heading-lg">Управление кольцами</h1>
              <p v-if="currentGroup" class="text-gray-600 mt-2">
                Группа: {{ currentGroup.nameRu || currentGroup.typeCode }}
              </p>
            </div>
          <div class="flex gap-3">
            <button
              v-if="groupId"
              @click="goToGroups"
              class="px-5 py-2.5 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-colors font-semibold"
            >
              К группам
            </button>
            <router-link
              to="/admin"
              class="px-5 py-2.5 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-colors font-semibold"
            >
              Назад
            </router-link>
          </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Поиск по номеру"
              class="input pl-12 pr-10"
              @input="applyFilters"
            />
            <button
              v-if="filters.search"
              @click="clearSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors z-10"
              title="Очистить поиск"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <label class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
            <input
              v-model="filters.inStockOnly"
              type="checkbox"
              class="w-4 h-4 text-blue-700 rounded focus:ring-blue-600"
              @change="applyFilters"
            />
            <span class="text-gray-700 font-medium whitespace-nowrap">Только с наличием</span>
          </label>
          <label class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
            <input
              v-model="filters.withPriceOnly"
              type="checkbox"
              class="w-4 h-4 text-blue-700 rounded focus:ring-blue-600"
              @change="applyFilters"
            />
            <span class="text-gray-700 font-medium whitespace-nowrap">Только с ценой</span>
          </label>
        </div>
      </div>

      <div v-if="loading" class="glass-card rounded-2xl text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-blue-700"></div>
        <p class="mt-4 text-body">Загрузка...</p>
      </div>

      <div v-else-if="filteredRings.length === 0" class="glass-card rounded-3xl p-16 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="mt-4 text-gray-600 font-semibold">Кольца не найдены</p>
        <p class="mt-2 text-gray-500">Попробуйте изменить параметры поиска</p>
      </div>

      <div v-else class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full table-fixed">
            <colgroup>
              <col style="width: 80px;">
              <col style="width: 200px;">
              <col style="width: 100px;">
              <col style="width: 200px;">
              <col style="width: 120px;">
              <col style="width: 140px;">
              <col style="width: 120px;">
              <col style="width: auto;">
            </colgroup>
            <thead class="bg-white/60">
              <tr>
                <th 
                  @click="sortBy('id')"
                  class="px-4 py-3 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    ID
                    <svg v-if="sortField === 'id'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th 
                  @click="sortBy('partNumber')"
                  class="px-4 py-3 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Номер
                    <svg v-if="sortField === 'partNumber'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th 
                  @click="sortBy('group')"
                  class="px-4 py-3 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Группа
                    <svg v-if="sortField === 'group'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th class="px-4 py-3 text-left text-base font-bold text-gray-900">Размеры</th>
                <th 
                  @click="sortBy('price')"
                  class="px-4 py-3 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Цена
                    <svg v-if="sortField === 'price'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th 
                  @click="sortBy('inStock')"
                  class="px-4 py-3 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    В наличии
                    <svg v-if="sortField === 'inStock'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th 
                  @click="sortBy('photo')"
                  class="px-4 py-3 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Фото
                    <svg v-if="sortField === 'photo'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th class="px-4 py-3 text-left text-base font-bold text-gray-900">Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="ring in paginatedRings"
                :key="ring.id"
                class="border-t border-gray-200/50 hover:bg-white/40 transition-all"
              >
                <td class="px-4 py-3 text-gray-900 font-semibold">{{ ring.id }}</td>
                
                <!-- Номер (Артикул) -->
                <td class="px-4 py-3">
                  <div v-if="editingField.ringId === ring.id && editingField.field === 'partNumber'">
                    <input
                      ref="editInput"
                      v-model="editingField.value"
                      type="text"
                      class="input w-full text-gray-900 font-bold text-sm px-2 py-1"
                      @keyup.enter="saveField(ring)"
                      @keyup.esc="cancelEdit"
                      autofocus
                    />
                  </div>
                  <div
                    v-else
                    class="flex items-center gap-2"
                  >
                    <div
                      @click="startEdit(ring, 'partNumber', ring.partNumber)"
                      class="text-gray-900 font-bold cursor-pointer hover:bg-blue-50 px-2 py-1 rounded transition-colors overflow-hidden text-ellipsis flex-1"
                      title="Нажмите для редактирования"
                    >
                      {{ ring.partNumber }}
                    </div>
                    <button
                      @click="copyToClipboard(`partNumber-${ring.id}`, ring.partNumber)"
                      class="p-1 transition-colors rounded flex-shrink-0"
                      :class="copiedItems[`partNumber-${ring.id}`] ? 'text-green-600' : 'text-gray-400 hover:text-blue-700 hover:bg-blue-50'"
                      :title="copiedItems[`partNumber-${ring.id}`] ? 'Скопировано!' : 'Копировать номер'"
                    >
                      <svg v-if="copiedItems[`partNumber-${ring.id}`]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>
                </td>
                
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <span class="text-gray-700">{{ getGroupName(ring.ringGroup) }}</span>
                    <button
                      @click="copyToClipboard(`group-${ring.id}`, getGroupName(ring.ringGroup))"
                      class="p-1 transition-colors rounded"
                      :class="copiedItems[`group-${ring.id}`] ? 'text-green-600' : 'text-gray-400 hover:text-blue-700 hover:bg-blue-50'"
                      :title="copiedItems[`group-${ring.id}`] ? 'Скопировано!' : 'Копировать группу'"
                    >
                      <svg v-if="copiedItems[`group-${ring.id}`]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>
                </td>
                
                <!-- Размеры -->
                <td class="px-4 py-3">
                  <div v-if="editingField.ringId === ring.id && editingField.field === 'dimensions'">
                    <input
                      v-model="editingField.value"
                      type="text"
                      class="input w-full text-xs px-2 py-1"
                      placeholder="10.5, 12, 8.2"
                      @keyup.esc="cancelEdit"
                      autofocus
                    />
                  </div>
                  <div
                    v-else
                    @click="startEdit(ring, 'dimensions', ring.dimensions ? ring.dimensions.join(', ') : '')"
                    class="flex flex-wrap gap-1 cursor-pointer hover:bg-blue-50 px-2 py-1 rounded transition-colors min-h-[2rem]"
                    title="Нажмите для редактирования"
                  >
                    <span
                      v-for="(dim, index) in ring.dimensions"
                      :key="index"
                      class="px-2 py-1 bg-gray-200 rounded text-xs"
                    >
                      {{ dim || '—' }}
                    </span>
                    <span v-if="!ring.dimensions || ring.dimensions.length === 0" class="text-gray-400">—</span>
                  </div>
                </td>
                
                <!-- Цена -->
                <td class="px-4 py-3">
                  <div v-if="editingField.ringId === ring.id && editingField.field === 'price'">
                    <input
                      v-model="editingField.value"
                      type="number"
                      step="0.01"
                      class="input w-full text-gray-900 font-semibold text-sm px-2 py-1"
                      @keyup.enter="saveField(ring)"
                      @keyup.esc="cancelEdit"
                      autofocus
                    />
                  </div>
                  <div
                    v-else
                    @click="startEdit(ring, 'price', ring.price || '')"
                    class="text-gray-900 font-semibold cursor-pointer hover:bg-blue-50 px-2 py-1 rounded transition-colors"
                    title="Нажмите для редактирования"
                  >
                    {{ ring.price ? `${ring.price} ₽` : '—' }}
                  </div>
                </td>
                
                <!-- Наличие -->
                <td class="px-4 py-3">
                  <div v-if="editingField.ringId === ring.id && editingField.field === 'inStock'">
                    <input
                      v-model.number="editingField.value"
                      type="number"
                      min="0"
                      class="input w-full font-semibold text-sm px-2 py-1"
                      @keyup.enter="saveField(ring)"
                      @keyup.esc="cancelEdit"
                      autofocus
                    />
                  </div>
                  <div
                    v-else
                    @click="startEdit(ring, 'inStock', ring.inStock)"
                    class="font-semibold cursor-pointer hover:bg-blue-50 px-2 py-1 rounded transition-colors"
                    :class="ring.inStock > 0 ? 'text-green-600' : 'text-gray-500'"
                    title="Нажмите для редактирования"
                  >
                    {{ ring.inStock }}
                  </div>
                </td>
                
                <td class="px-4 py-3">
                  <div v-if="ring.photos && ring.photos.length > 0">
                    <img
                      :src="ring.photos[0]"
                      alt="Фото"
                      class="w-16 h-16 object-contain rounded-lg cursor-pointer hover:shadow-lg transition-shadow"
                      @click="openPhotoGallery(ring.photos, ring.partNumber)"
                    />
                    <p v-if="ring.photos.length > 1" class="text-xs text-gray-500 mt-1 text-center">
                      +{{ ring.photos.length - 1 }}
                    </p>
                  </div>
                  <span v-else class="text-gray-400">—</span>
                </td>
                
                <td class="px-4 py-3">
                  <div class="flex gap-2 flex-wrap">
                    <button
                      v-if="editingField.ringId === ring.id"
                      @click="saveField(ring)"
                      class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors font-semibold"
                    >
                      Сохранить
                    </button>
                    <button
                      v-if="editingField.ringId === ring.id"
                      @click="cancelEdit"
                      class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors font-semibold"
                    >
                      Отмена
                    </button>
                    <button
                      v-if="editingField.ringId !== ring.id"
                      @click="openEditModal(ring)"
                      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                    >
                      Редактировать
                    </button>
                    <button
                      v-if="editingField.ringId !== ring.id"
                      @click="copyRing(ring)"
                      class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold"
                      title="Копировать кольцо"
                    >
                      Копировать
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 p-6 border-t border-gray-200">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Назад
          </button>
          <span class="text-gray-900 font-semibold">
            Страница {{ currentPage }} из {{ totalPages }}
          </span>
          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Вперед
          </button>
        </div>
      </div>
    </div>
  </div>

  <EditRingModal
    :visible="editModalVisible"
    :ring="selectedRing"
    :groups="groups"
    @close="closeEditModal"
    @saved="onRingSaved"
    @deleted="onRingDeleted"
  />

  <PhotoGalleryModal
    :visible="photoGalleryVisible"
    :photos="galleryPhotos"
    :title="galleryTitle"
    @close="closePhotoGallery"
  />

  <ImageModal
    :visible="imageModalVisible"
    :imageUrl="selectedImage"
    @close="closeImageModal"
  />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/config/axios'
import EditRingModal from '@/components/EditRingModal.vue'
import PhotoGalleryModal from '@/components/PhotoGalleryModal.vue'
import ImageModal from '@/components/ImageModal.vue'

const router = useRouter()
const route = useRoute()

const rings = ref([])
const groups = ref([])
const loading = ref(true)
const editModalVisible = ref(false)
const selectedRing = ref(null)
const editingField = ref({
  ringId: null,
  field: null,
  value: null
})
const photoGalleryVisible = ref(false)
const galleryPhotos = ref([])
const galleryTitle = ref('')
const imageModalVisible = ref(false)
const selectedImage = ref('')
const filters = ref({
  search: '',
  inStockOnly: false,
  withPriceOnly: false
})
const currentPage = ref(1)
const itemsPerPage = 20
const sortField = ref('id')
const sortDirection = ref('asc')
const copiedItems = ref({})

const groupId = computed(() => route.query.groupId)

const currentGroup = computed(() => {
  if (!groupId.value) return null
  return groups.value.find(g => g.id === parseInt(groupId.value))
})

const filteredRings = computed(() => {
  let filtered = rings.value
  
  // Поиск
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase()
    filtered = filtered.filter(ring => 
      ring.partNumber?.toLowerCase().includes(searchLower)
    )
  }
  
  // Фильтр по наличию
  if (filters.value.inStockOnly) {
    filtered = filtered.filter(ring => ring.inStock > 0)
  }
  
  // Фильтр по цене
  if (filters.value.withPriceOnly) {
    filtered = filtered.filter(ring => ring.price && ring.price > 0)
  }
  
  // Сортировка
  filtered = [...filtered].sort((a, b) => {
    let aVal, bVal
    
    switch (sortField.value) {
      case 'id':
        aVal = parseInt(a.id) || 0
        bVal = parseInt(b.id) || 0
        break
      case 'partNumber':
        aVal = (a.partNumber || '').toLowerCase()
        bVal = (b.partNumber || '').toLowerCase()
        break
      case 'group':
        aVal = getGroupName(a.ringGroup).toLowerCase()
        bVal = getGroupName(b.ringGroup).toLowerCase()
        break
      case 'price':
        aVal = parseFloat(a.price) || 0
        bVal = parseFloat(b.price) || 0
        break
      case 'inStock':
        aVal = parseInt(a.inStock) || 0
        bVal = parseInt(b.inStock) || 0
        break
      case 'photo':
        aVal = a.photos && a.photos.length > 0 ? 1 : 0
        bVal = b.photos && b.photos.length > 0 ? 1 : 0
        break
      default:
        return 0
    }
    
    if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1
    if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1
    return 0
  })
  
  return filtered
})

const totalPages = computed(() => Math.ceil(filteredRings.value.length / itemsPerPage))

const paginatedRings = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredRings.value.slice(start, end)
})

function applyFilters() {
  currentPage.value = 1
}

function sortBy(field) {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
}

async function fetchRings() {
  loading.value = true
  try {
    const response = await apiClient.get('/api/admin/rings')
    let allRings = response.data || []
    
    if (groupId.value) {
      allRings = allRings.filter(ring => ring.ringGroup === parseInt(groupId.value))
    }
    
    rings.value = allRings
  } catch (error) {
    console.error('Ошибка загрузки колец:', error)
  } finally {
    loading.value = false
  }
}

async function fetchGroups() {
  try {
    const response = await apiClient.get('/api/admin/groups')
    groups.value = response.data || []
  } catch (error) {
    console.error('Ошибка загрузки групп:', error)
  }
}

function getGroupName(groupId) {
  const group = groups.value.find(g => g.id === groupId)
  return group ? group.typeCode : '—'
}

async function openEditModal(ring) {
  selectedRing.value = ring
  // Загружаем группы, если их еще нет
  if (groups.value.length === 0) {
    await fetchGroups()
  }
  editModalVisible.value = true
}

async function copyRing(ring) {
  if (!confirm(`Скопировать кольцо "${ring.partNumber}"?`)) {
    return
  }
  
  try {
    const ringData = {
      partNumber: ring.partNumber + ' (копия)',
      dimensions: ring.dimensions || [],
      price: ring.price || null,
      inStock: ring.inStock || 0,
      photos: ring.photos || [],
      isHidden: ring.isHidden || false,
      ringGroup: ring.ringGroup
    }
    
    await apiClient.post('/api/admin/rings', ringData)
    await fetchRings()
    alert('Кольцо успешно скопировано')
  } catch (error) {
    console.error('Ошибка при копировании кольца:', error)
    alert('Ошибка при копировании кольца')
  }
}

function startEdit(ring, field, value) {
  editingField.value = {
    ringId: ring.id,
    field: field,
    value: value
  }
}

function cancelEdit() {
  editingField.value = {
    ringId: null,
    field: null,
    value: null
  }
}

async function saveField(ring) {
  const field = editingField.value.field
  const value = editingField.value.value
  
  try {
    const updateData = {}
    
    if (field === 'partNumber') {
      if (!value || value.trim() === '') {
        alert('Номер не может быть пустым')
        return
      }
      updateData.partNumber = value.trim()
    } else if (field === 'dimensions') {
      // Парсинг размеров из строки через запятую
      if (value.trim()) {
        const dimensions = value
          .split(',')
          .map(d => d.trim())
          .filter(d => d !== '')
        updateData.dimensions = Array.from(dimensions)
      } else {
        updateData.dimensions = []
      }
    } else if (field === 'price') {
      updateData.price = value === '' ? null : value
    } else if (field === 'inStock') {
      if (value < 0) {
        alert('Количество не может быть отрицательным')
        return
      }
      updateData.inStock = parseInt(value) || 0
    }
    
    await apiClient.patch(`/api/admin/rings/${ring.id}`, updateData)
    
    // Обновляем данные локально без перезагрузки таблицы
    const ringIndex = rings.value.findIndex(r => r.id === ring.id)
    if (ringIndex !== -1) {
      rings.value[ringIndex] = {
        ...rings.value[ringIndex],
        ...updateData
      }
    }
    
    cancelEdit()
  } catch (error) {
    console.error('Ошибка при сохранении:', error)
    alert('Ошибка при сохранении изменений')
  }
}

function closeEditModal() {
  editModalVisible.value = false
  selectedRing.value = null
}

function onRingSaved() {
  closeEditModal()
  fetchRings()
}

function onRingDeleted() {
  closeEditModal()
  fetchRings()
}

function goToGroups() {
  router.push({ name: 'AdminGroups' })
}

function openImageModal(url) {
  selectedImage.value = url
  imageModalVisible.value = true
}

function closeImageModal() {
  imageModalVisible.value = false
}

function openPhotoGallery(photos, title) {
  galleryPhotos.value = photos
  galleryTitle.value = title
  photoGalleryVisible.value = true
}

function closePhotoGallery() {
  photoGalleryVisible.value = false
}

function clearSearch() {
  filters.value.search = ''
  applyFilters()
}

async function copyToClipboard(itemId, text) {
  try {
    let success = false
    
    // Проверяем доступность Clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
      await navigator.clipboard.writeText(text)
      success = true
    } else {
      // Fallback метод для старых браузеров или небезопасного контекста
      const textArea = document.createElement('textarea')
      textArea.value = text
      textArea.style.position = 'fixed'
      textArea.style.left = '-999999px'
      textArea.style.top = '-999999px'
      document.body.appendChild(textArea)
      textArea.focus()
      textArea.select()
      try {
        success = document.execCommand('copy')
      } catch (err) {
        console.error('Fallback: Ошибка копирования', err)
      }
      document.body.removeChild(textArea)
    }
    
    if (success) {
      // Показываем галочку
      copiedItems.value[itemId] = true
      
      // Через 2 секунды возвращаем иконку копирования
      setTimeout(() => {
        copiedItems.value[itemId] = false
      }, 2000)
    } else {
      alert('Ошибка при копировании')
    }
  } catch (error) {
    console.error('Ошибка копирования:', error)
    alert('Ошибка при копировании')
  }
}

onMounted(async () => {
  await fetchGroups()
  await fetchRings()
})
</script>
