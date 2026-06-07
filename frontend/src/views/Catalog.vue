<template>
  <div>
    <div class="px-4 md:px-8 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="glass-card-strong rounded-3xl p-10 mb-8">
          <h1 class="heading-xl mb-8">Каталог колец</h1>

          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
              <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Поиск по названию, типу или номеру кольца"
                class="input pl-12"
                @input="fetchGroups"
              />
            </div>
          </div>
        </div>

      <div v-if="loading" class="glass-card rounded-2xl text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-purple-600"></div>
        <p class="mt-4 text-body">Загрузка...</p>
      </div>

      <div v-else-if="groups.length === 0" class="glass-card rounded-3xl p-16 text-center">
        <svg class="w-24 h-24 mx-auto mb-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="text-body text-xl">Группы не найдены</p>
      </div>

      <div v-else class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-white/60">
              <tr>
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
                  @click="sortBy('photo')"
                  class="px-6 py-4 text-left text-base font-bold text-gray-900 cursor-pointer hover:bg-white/80 transition-colors select-none"
                >
                  <div class="flex items-center gap-2">
                    Профиль
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
                v-for="group in sortedGroups"
                :key="group.id"
                class="border-t border-gray-200/50 hover:bg-white/40 transition-all"
              >
                <td class="px-6 py-4">
                  <div class="font-bold text-gray-900 text-base">
                    {{ group.nameRu || group.typeCode }}
                  </div>
                  <div v-if="group.materialEn" class="text-sm text-purple-600 mt-1">
                    Материал: {{ group.materialEn }}
                  </div>
                </td>
                <td class="px-6 py-4 text-gray-700 font-medium">
                  {{ group.typeCode }}
                </td>
                <td class="px-6 py-4">
                  <img
                    v-if="group.photoUrl"
                    :src="group.photoUrl"
                    :alt="group.nameRu || group.typeCode"
                    class="w-20 h-20 object-contain rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow"
                    @click="openImageModal(group.photoUrl, group.nameRu || group.typeCode)"
                  />
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openGroupModal(group)"
                    class="px-5 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold"
                  >
                    Открыть
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

    <RingsModal
      :visible="ringsModalVisible"
      :group="selectedGroup"
      @close="closeGroupModal"
    />

    <ImageModal
      :visible="imageModalVisible"
      :imageUrl="selectedImage"
      :alt="selectedImageAlt"
      @close="closeImageModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/config/axios'
import RingsModal from '@/components/RingsModal.vue'
import ImageModal from '@/components/ImageModal.vue'

const filters = ref({
  search: ''
})

const groups = ref([])
const loading = ref(true)
const ringsModalVisible = ref(false)
const selectedGroup = ref(null)
const imageModalVisible = ref(false)
const selectedImage = ref('')
const selectedImageAlt = ref('')
const sortField = ref('id')
const sortDirection = ref('asc')

const sortedGroups = computed(() => {
  let sorted = [...groups.value]
  
  sorted.sort((a, b) => {
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
      case 'photo':
        aVal = a.photoUrl ? 1 : 0
        bVal = b.photoUrl ? 1 : 0
        break
      default:
        return 0
    }
    
    if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1
    if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1
    return 0
  })
  
  return sorted
})

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
    const response = await apiClient.get('/api/catalog/groups', {
      params: {
        search: filters.value.search,
        inStockOnly: true
      }
    })
    groups.value = response.data || []
  } catch (error) {
    console.error('Ошибка загрузки групп:', error)
  } finally {
    loading.value = false
  }
}

function openGroupModal(group) {
  selectedGroup.value = group
  ringsModalVisible.value = true
}

function closeGroupModal() {
  ringsModalVisible.value = false
  selectedGroup.value = null
}

function openImageModal(url, alt) {
  selectedImage.value = url
  selectedImageAlt.value = alt
  imageModalVisible.value = true
}

function closeImageModal() {
  imageModalVisible.value = false
}

onMounted(() => {
  fetchGroups()
})
</script>
