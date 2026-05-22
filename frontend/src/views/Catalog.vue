<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
        <h1 class="text-4xl font-bold text-gray-900 mb-6">Каталог колец</h1>

        <div class="flex flex-col sm:flex-row gap-4">
          <input
            v-model="filters.partNumber"
            type="text"
            placeholder="Поиск по артикулу"
            class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[rgb(126,216,153)] focus:border-[rgb(126,216,153)]"
            @input="fetchGroups"
          />
          
          <label class="flex items-center gap-3 px-4 py-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
            <input
              v-model="filters.inStockOnly"
              type="checkbox"
              class="w-5 h-5 text-[rgb(126,216,153)] rounded focus:ring-[rgb(126,216,153)]"
              @change="fetchGroups"
            />
            <span class="text-gray-700 font-medium">Только в наличии</span>
          </label>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-[rgb(126,216,153)]"></div>
        <p class="mt-4 text-gray-600">Загрузка...</p>
      </div>

      <div v-else-if="groups.length === 0" class="bg-white rounded-2xl shadow-lg p-12 text-center">
        <div class="text-6xl mb-4">🔍</div>
        <p class="text-gray-600 text-lg">Группы не найдены</p>
      </div>

      <div v-else class="space-y-6">
        <div
          v-for="group in groups"
          :key="group.id"
          class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition"
        >
          <div 
            class="flex justify-between items-center cursor-pointer p-6 hover:bg-gray-50 transition"
            @click="toggleGroup(group.id)"
          >
            <div class="flex gap-6 items-center flex-1">
              <img
                v-if="group.photoUrl"
                :src="group.photoUrl"
                :alt="group.nameRu || group.typeCode"
                class="w-20 h-20 object-contain rounded-xl"
              />
              <div>
                <h3 class="text-xl font-semibold text-gray-900">
                  {{ group.nameRu || group.typeCode }}
                </h3>
                <p class="text-gray-500 text-sm mt-1">{{ group.typeCode }}</p>
                <p v-if="group.materialEn" class="text-[rgb(126,216,153)] text-sm mt-1 font-medium">
                  {{ group.materialEn }}
                </p>
              </div>
            </div>
            <button class="text-3xl text-gray-400 hover:text-[rgb(126,216,153)] transition">
              {{ expandedGroups.has(group.id) ? '▼' : '▶' }}
            </button>
          </div>

          <div v-if="expandedGroups.has(group.id)" class="border-t border-gray-200 p-6 bg-gray-50">
            <img
              v-if="group.dimensionsPhotoUrl"
              :src="group.dimensionsPhotoUrl"
              alt="Размерная схема"
              class="max-w-sm mb-6 rounded-xl shadow-md"
            />
            
            <div v-if="loadingRings.has(group.id)" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-[rgb(126,216,153)]"></div>
              <p class="mt-3 text-gray-600">Загрузка размеров...</p>
            </div>

            <div v-else-if="ringsByGroup.get(group.id)?.length" class="overflow-x-auto">
              <table class="w-full bg-white rounded-xl overflow-hidden">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Артикул</th>
                    <th 
                      v-for="(name, index) in getColumnNames(group)" 
                      :key="index"
                      class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                    >
                      {{ name }}
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Цена</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Наличие</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="ring in ringsByGroup.get(group.id)" 
                    :key="ring.id"
                    class="border-t border-gray-100 hover:bg-gray-50 transition"
                  >
                    <td class="px-4 py-3">
                      <strong class="text-gray-900">{{ ring.partNumber }}</strong>
                    </td>
                    <td 
                      v-for="(dim, index) in ring.dimensions" 
                      :key="index"
                      class="px-4 py-3 text-gray-700"
                    >
                      {{ dim }}
                    </td>
                    <td class="px-4 py-3 text-gray-900 font-medium">
                      {{ ring.price ? `${ring.price} ₽` : '—' }}
                    </td>
                    <td class="px-4 py-3">
                      <span 
                        :class="ring.inStock 
                          ? 'text-[rgb(126,216,153)] font-semibold' 
                          : 'text-gray-400'"
                      >
                        {{ ring.inStock ? 'В наличии' : 'Под заказ' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <p v-else class="text-center py-8 text-gray-500">Нет доступных размеров</p>
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
