<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="glass-card-strong rounded-3xl p-10 mb-8">
        <h1 class="heading-xl mb-8">Каталог колец</h1>

        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="filters.partNumber"
              type="text"
              placeholder="Поиск по артикулу"
              class="input pl-12"
              @input="fetchGroups"
            />
          </div>
          
          <label class="flex items-center gap-3 px-6 py-4 glass-card rounded-xl cursor-pointer hover:bg-white/60 transition-all">
            <input
              v-model="filters.inStockOnly"
              type="checkbox"
              class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500"
              @change="fetchGroups"
            />
            <span class="text-gray-900 font-semibold text-base">Только в наличии</span>
          </label>
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

      <div v-else class="space-y-6">
        <div
          v-for="group in groups"
          :key="group.id"
          class="glass-card rounded-3xl overflow-hidden hover:shadow-glass-lg transition-smooth"
        >
          <div 
            class="flex justify-between items-center cursor-pointer p-8 hover:bg-white/40 transition-all"
            @click="toggleGroup(group.id)"
          >
            <div class="flex gap-6 items-center flex-1">
              <img
                v-if="group.photoUrl"
                :src="group.photoUrl"
                :alt="group.nameRu || group.typeCode"
                class="w-24 h-24 object-contain rounded-2xl shadow-md"
              />
              <div>
                <h3 class="heading-md mb-2">
                  {{ group.nameRu || group.typeCode }}
                </h3>
                <p class="text-gray-600 text-base font-medium">{{ group.typeCode }}</p>
                <p v-if="group.materialEn" class="text-purple-600 text-base mt-2 font-bold">
                  {{ group.materialEn }}
                </p>
              </div>
            </div>
            <svg 
              class="w-8 h-8 text-gray-400 transition-transform duration-300" 
              :class="{ 'rotate-90': expandedGroups.has(group.id) }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <div v-if="expandedGroups.has(group.id)" class="border-t border-gray-300/50 p-8 bg-gradient-to-br from-white/30 to-white/10">
            <img
              v-if="group.dimensionsPhotoUrl"
              :src="group.dimensionsPhotoUrl"
              alt="Размерная схема"
              class="max-w-md mb-8 rounded-2xl shadow-glass"
            />
            
            <div v-if="loadingRings.has(group.id)" class="text-center py-12">
              <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-gray-300 border-t-purple-600"></div>
              <p class="mt-4 text-body">Загрузка размеров...</p>
            </div>

            <div v-else-if="ringsByGroup.get(group.id)?.length" class="overflow-x-auto rounded-2xl">
              <table class="w-full glass-card-strong overflow-hidden">
                <thead class="bg-white/60">
                  <tr>
                    <th class="px-5 py-4 text-left text-base font-bold text-gray-900">Артикул</th>
                    <th 
                      v-for="(name, index) in getColumnNames(group)" 
                      :key="index"
                      class="px-5 py-4 text-left text-base font-bold text-gray-900"
                    >
                      {{ name }}
                    </th>
                    <th class="px-5 py-4 text-left text-base font-bold text-gray-900">Цена</th>
                    <th class="px-5 py-4 text-left text-base font-bold text-gray-900">Наличие</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="ring in ringsByGroup.get(group.id)" 
                    :key="ring.id"
                    class="border-t border-gray-200/50 hover:bg-white/40 transition-all"
                  >
                    <td class="px-5 py-4">
                      <strong class="text-gray-900 text-base">{{ ring.partNumber }}</strong>
                    </td>
                    <td 
                      v-for="(dim, index) in ring.dimensions" 
                      :key="index"
                      class="px-5 py-4 text-gray-700 font-medium text-base"
                    >
                      {{ dim }}
                    </td>
                    <td class="px-5 py-4 text-gray-900 font-bold text-base">
                      {{ ring.price ? `${ring.price} ₽` : '—' }}
                    </td>
                    <td class="px-5 py-4">
                      <span 
                        class="inline-flex items-center gap-2 font-bold text-base"
                        :class="ring.inStock ? 'text-green-600' : 'text-gray-500'"
                      >
                        <svg v-if="ring.inStock" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ ring.inStock ? 'В наличии' : 'Под заказ' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <p v-else class="text-center py-12 text-body text-xl">Нет доступных размеров</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/config/axios'

const filters = ref({
  partNumber: '',
  inStockOnly: false,
})

const groups = ref([])
const ringsByGroup = ref(new Map())
const expandedGroups = ref(new Set())
const loadingRings = ref(new Set())
const loading = ref(true)

async function fetchGroups() {
  loading.value = true
  try {
    const response = await apiClient.get('/api/catalog/groups', {
      params: filters.value
    })
    groups.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch groups:', error)
  } finally {
    loading.value = false
  }
}

async function toggleGroup(groupId) {
  if (expandedGroups.value.has(groupId)) {
    expandedGroups.value.delete(groupId)
  } else {
    expandedGroups.value.add(groupId)
    
    if (!ringsByGroup.value.has(groupId)) {
      await fetchRings(groupId)
    }
  }
}

async function fetchRings(groupId) {
  loadingRings.value.add(groupId)
  try {
    const response = await apiClient.get('/api/catalog/rings', {
      params: {
        groupId,
        inStockOnly: filters.value.inStockOnly,
      }
    })
    ringsByGroup.value.set(groupId, response.data || [])
  } catch (error) {
    console.error('Failed to fetch rings:', error)
  } finally {
    loadingRings.value.delete(groupId)
  }
}

function getColumnNames(group) {
  if (!group.columnNames) return []
  
  const names = []
  const columnMap = group.columnNames
  
  let i = 1
  while (columnMap[i.toString()]) {
    names.push(columnMap[i.toString()])
    i++
  }
  
  return names
}

onMounted(() => {
  fetchGroups()
})
</script>
