<template>
  <div>
    <div class="px-4 md:px-8 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="glass-card-strong rounded-3xl p-10 mb-8">
          <div class="flex justify-between items-center mb-6">
            <h1 class="heading-xl">Управление группами</h1>
            <router-link
              to="/admin"
              class="px-5 py-2.5 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-colors font-semibold"
            >
              Назад
            </router-link>
          </div>
          
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
              <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Поиск по названию, типу"
                class="input pl-12"
                @input="applyFilters"
              />
            </div>
            <label class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
              <input
                v-model="filters.inStockOnly"
                type="checkbox"
                class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"
                @change="applyFilters"
              />
              <span class="text-gray-700 font-medium whitespace-nowrap">Только с наличием</span>
            </label>
          </div>
        </div>

      <div v-if="loading" class="glass-card rounded-2xl text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-purple-600"></div>
        <p class="mt-4 text-body">Загрузка...</p>
      </div>

      <div v-else class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-white/60">
              <tr>
                <th 
                  @click="sortBy('id')"
                  class="px-6 py-4 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    ID
                    <svg v-if="sortField === 'id'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th 
                  @click="sortBy('name')"
                  class="px-6 py-4 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Название
                    <svg v-if="sortField === 'name'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th 
                  @click="sortBy('typeCode')"
                  class="px-6 py-4 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Тип
                    <svg v-if="sortField === 'typeCode'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th 
                  @click="sortBy('brand')"
                  class="px-6 py-4 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Бренд
                    <svg v-if="sortField === 'brand'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th 
                  @click="sortBy('photo')"
                  class="px-6 py-4 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Фото
                    <svg v-if="sortField === 'photo'" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sortDirection === 'asc' }" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="group in paginatedGroups"
                :key="group.id"
                class="border-t border-gray-200/50 hover:bg-white/40 transition-all"
              >
                <td class="px-6 py-4 text-gray-900 font-semibold">{{ group.id }}</td>
                <td class="px-6 py-4">
                  <div class="font-bold text-gray-900">{{ group.nameRu || '—' }}</div>
                  <div v-if="group.nameEn" class="text-sm text-gray-600 mt-1">{{ group.nameEn }}</div>
                </td>
                <td class="px-6 py-4 text-gray-700 font-medium">{{ group.typeCode }}</td>
                <td class="px-6 py-4 text-gray-700">{{ group.brand || '—' }}</td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <img
                      v-if="group.photoUrl"
                      :src="group.photoUrl"
                      alt="Фото"
                      class="w-16 h-16 object-contain rounded-lg cursor-pointer hover:shadow-lg transition-shadow"
                      @click="openImageModal(group.photoUrl)"
                    />
                    <img
                      v-if="group.dimensionsPhotoUrl"
                      :src="group.dimensionsPhotoUrl"
                      alt="Размеры"
                      class="w-16 h-16 object-contain rounded-lg cursor-pointer hover:shadow-lg transition-shadow border-2 border-purple-200"
                      @click="openImageModal(group.dimensionsPhotoUrl)"
                    />
                    <span v-if="!group.photoUrl && !group.dimensionsPhotoUrl" class="text-gray-400">—</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <button
                      @click="openEditModal(group)"
                      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                    >
                      Редактировать
                    </button>
                    <button
                      @click="openRingsList(group)"
                      class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold"
                    >
                      Кольца
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

  <EditGroupModal
    :visible="editModalVisible"
    :group="selectedGroup"
    @close="closeEditModal"
    @saved="onGroupSaved"
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
import { useRouter } from 'vue-router'
import apiClient from '@/config/axios'
import EditGroupModal from '@/components/EditGroupModal.vue'
import ImageModal from '@/components/ImageModal.vue'

const router = useRouter()

const groups = ref([])
const loading = ref(true)
const editModalVisible = ref(false)
const selectedGroup = ref(null)
const imageModalVisible = ref(false)
const selectedImage = ref('')
const filters = ref({
  search: '',
  inStockOnly: false
})
const currentPage = ref(1)
const itemsPerPage = 20
const sortField = ref('id')
const sortDirection = ref('asc')
const allRings = ref([])

const filteredGroups = computed(() => {
  let filtered = groups.value
  
  // Поиск
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase()
    filtered = filtered.filter(group => {
      return (
        group.nameRu?.toLowerCase().includes(searchLower) ||
        group.nameEn?.toLowerCase().includes(searchLower) ||
        group.typeCode?.toLowerCase().includes(searchLower)
      )
    })
  }
  
  // Фильтр по наличию
  if (filters.value.inStockOnly) {
    filtered = filtered.filter(group => {
      const groupRings = allRings.value.filter(ring => ring.ringGroup === group.id)
      return groupRings.some(ring => ring.inStock > 0)
    })
  }
  
  // Сортировка
  filtered = [...filtered].sort((a, b) => {
    let aVal, bVal
    
    switch (sortField.value) {
      case 'id':
        aVal = parseInt(a.id) || 0
        bVal = parseInt(b.id) || 0
        break
      case 'name':
        aVal = (a.nameRu || a.typeCode || '').toLowerCase()
        bVal = (b.nameRu || b.typeCode || '').toLowerCase()
        break
      case 'typeCode':
        aVal = (a.typeCode || '').toLowerCase()
        bVal = (b.typeCode || '').toLowerCase()
        break
      case 'brand':
        aVal = (a.brand || '').toLowerCase()
        bVal = (b.brand || '').toLowerCase()
        break
      case 'photo':
        aVal = a.photoUrl || a.dimensionsPhotoUrl ? 1 : 0
        bVal = b.photoUrl || b.dimensionsPhotoUrl ? 1 : 0
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

const totalPages = computed(() => Math.ceil(filteredGroups.value.length / itemsPerPage))

const paginatedGroups = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredGroups.value.slice(start, end)
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

async function fetchGroups() {
  loading.value = true
  try {
    const response = await apiClient.get('/api/admin/groups')
    groups.value = response.data || []
  } catch (error) {
    console.error('Ошибка загрузки групп:', error)
  } finally {
    loading.value = false
  }
}

async function fetchRings() {
  try {
    const response = await apiClient.get('/api/admin/rings')
    allRings.value = response.data || []
  } catch (error) {
    console.error('Ошибка загрузки колец:', error)
  }
}

function openEditModal(group) {
  selectedGroup.value = group
  editModalVisible.value = true
}

function closeEditModal() {
  editModalVisible.value = false
  selectedGroup.value = null
}

function onGroupSaved() {
  closeEditModal()
  fetchGroups()
}

function openRingsList(group) {
  router.push({ name: 'AdminRings', query: { groupId: group.id } })
}

function openImageModal(url) {
  selectedImage.value = url
  imageModalVisible.value = true
}

function closeImageModal() {
  imageModalVisible.value = false
}

onMounted(async () => {
  await fetchGroups()
  await fetchRings()
})
</script>
