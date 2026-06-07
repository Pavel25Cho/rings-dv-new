<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="glass-card-strong rounded-3xl p-10 mb-8">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="heading-xl">Управление кольцами</h1>
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
              class="input pl-12"
              @input="applyFilters"
            />
          </div>
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
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">ID</th>
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">Номер</th>
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">Группа</th>
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">Размеры</th>
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">Цена</th>
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">В наличии</th>
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">Фото</th>
                <th class="px-6 py-4 text-left text-base font-bold text-gray-900">Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="ring in paginatedRings"
                :key="ring.id"
                class="border-t border-gray-200/50 hover:bg-white/40 transition-all"
              >
                <td class="px-6 py-4 text-gray-900 font-semibold">{{ ring.id }}</td>
                <td class="px-6 py-4 text-gray-900 font-bold">{{ ring.partNumber }}</td>
                <td class="px-6 py-4 text-gray-700">
                  {{ getGroupName(ring.ringGroup) }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="(dim, index) in ring.dimensions"
                      :key="index"
                      class="px-2 py-1 bg-gray-200 rounded text-sm"
                    >
                      {{ dim || '—' }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 text-gray-900 font-semibold">
                  {{ ring.price ? `${ring.price} ₽` : '—' }}
                </td>
                <td class="px-6 py-4">
                  <span
                    class="font-semibold"
                    :class="ring.inStock > 0 ? 'text-green-600' : 'text-gray-500'"
                  >
                    {{ ring.inStock }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <img
                    v-if="ring.photoUrl"
                    :src="ring.photoUrl"
                    alt="Фото"
                    class="w-16 h-16 object-contain rounded-lg cursor-pointer hover:shadow-lg transition-shadow"
                    @click="openImageModal(ring.photoUrl)"
                  />
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(ring)"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                  >
                    Редактировать
                  </button>
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
    @close="closeEditModal"
    @saved="onRingSaved"
  />

  <ImageModal
    :visible="imageModalVisible"
    :imageUrl="selectedImage"
    @close="closeImageModal"
  />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/config/axios'
import EditRingModal from '@/components/EditRingModal.vue'
import ImageModal from '@/components/ImageModal.vue'

const router = useRouter()
const route = useRoute()

const rings = ref([])
const groups = ref([])
const loading = ref(true)
const editModalVisible = ref(false)
const selectedRing = ref(null)
const imageModalVisible = ref(false)
const selectedImage = ref('')
const filters = ref({
  search: ''
})
const currentPage = ref(1)
const itemsPerPage = 20

const groupId = computed(() => route.query.groupId)

const currentGroup = computed(() => {
  if (!groupId.value) return null
  return groups.value.find(g => g.id === parseInt(groupId.value))
})

const filteredRings = computed(() => {
  let filtered = rings.value
  
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase()
    filtered = filtered.filter(ring => 
      ring.partNumber?.toLowerCase().includes(searchLower)
    )
  }
  
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
  return group ? (group.nameRu || group.typeCode) : '—'
}

function openEditModal(ring) {
  selectedRing.value = ring
  editModalVisible.value = true
}

function closeEditModal() {
  editModalVisible.value = false
  selectedRing.value = null
}

function onRingSaved() {
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

onMounted(async () => {
  await fetchGroups()
  await fetchRings()
})
</script>
