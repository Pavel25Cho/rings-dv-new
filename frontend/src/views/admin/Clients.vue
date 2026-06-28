<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="glass-card rounded-3xl p-8 mb-8">
        <div class="flex justify-between items-center">
          <h1 class="heading-lg">Управление клиентами</h1>
          <router-link 
            to="/admin" 
            class="btn btn-secondary"
          >
            ← Назад к панели
          </router-link>
        </div>
      </div>

      <div class="glass-card rounded-3xl p-8">
        <div class="mb-6">
          <div class="relative mb-4">
            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Поиск по email, имени или телефону..."
              class="input pl-12 pr-10 w-full"
              @input="debouncedSearch"
            />
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors z-10"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Email подтвержден -->
            <div class="relative">
              <button
                @click="toggleDropdown('emailVerified')"
                class="input w-full text-left flex items-center justify-between"
              >
                <span :class="filters.emailVerified === '' ? 'text-gray-400' : 'text-gray-900'">
                  {{ getEmailVerifiedLabel() }}
                </span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                v-if="openDropdown === 'emailVerified'"
                class="absolute z-20 mt-2 w-full bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden"
              >
                <button
                  @click="selectFilter('emailVerified', '')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Email: Все
                </button>
                <button
                  @click="selectFilter('emailVerified', 'true')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Email: Подтвержден
                </button>
                <button
                  @click="selectFilter('emailVerified', 'false')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Email: Не подтвержден
                </button>
              </div>
            </div>
            
            <!-- Статус аккаунта -->
            <div class="relative">
              <button
                @click="toggleDropdown('isBlocked')"
                class="input w-full text-left flex items-center justify-between"
              >
                <span :class="filters.isBlocked === '' ? 'text-gray-400' : 'text-gray-900'">
                  {{ getIsBlockedLabel() }}
                </span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                v-if="openDropdown === 'isBlocked'"
                class="absolute z-20 mt-2 w-full bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden"
              >
                <button
                  @click="selectFilter('isBlocked', '')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Статус: Все
                </button>
                <button
                  @click="selectFilter('isBlocked', 'false')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Статус: Активен
                </button>
                <button
                  @click="selectFilter('isBlocked', 'true')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Статус: Заблокирован
                </button>
              </div>
            </div>
            
            <!-- Сообщения в чате -->
            <div class="relative">
              <button
                @click="toggleDropdown('hasChat')"
                class="input w-full text-left flex items-center justify-between"
              >
                <span :class="filters.hasChat === '' ? 'text-gray-400' : 'text-gray-900'">
                  {{ getHasChatLabel() }}
                </span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                v-if="openDropdown === 'hasChat'"
                class="absolute z-20 mt-2 w-full bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden"
              >
                <button
                  @click="selectFilter('hasChat', '')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Сообщения: Все
                </button>
                <button
                  @click="selectFilter('hasChat', 'true')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Сообщения: Есть
                </button>
                <button
                  @click="selectFilter('hasChat', 'false')"
                  class="w-full text-left px-4 py-3 hover:bg-purple-50 transition-colors text-gray-700 font-medium"
                >
                  Сообщения: Нет
                </button>
              </div>
            </div>
            
            <!-- Кнопка сброса -->
            <div>
              <button
                @click="resetFilters"
                class="btn btn-secondary w-full"
              >
                Сбросить фильтры
              </button>
            </div>
          </div>
        </div>

        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-purple-600"></div>
          <p class="mt-4 text-gray-600">Загрузка...</p>
        </div>

        <div v-else-if="clients.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <p class="mt-4 text-gray-600 font-semibold">Клиенты не найдены</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-300/50">
                <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wide">Email</th>
                <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wide">Имя</th>
                <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wide">Телефон</th>
                <th class="text-center py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wide">Email</th>
                <th class="text-center py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wide">Статус</th>
                <th class="text-center py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wide">Чат</th>
                <th class="text-center py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wide">Дата регистрации</th>
                <th class="text-center py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wide">Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="client in clients" 
                :key="client.id"
                class="border-b border-gray-200/50 hover:bg-white/50 transition-colors"
              >
                <td class="py-4 px-4 text-gray-900">{{ client.email }}</td>
                <td class="py-4 px-4 text-gray-900">{{ client.name || '-' }}</td>
                <td class="py-4 px-4 text-gray-900">{{ client.phone || '-' }}</td>
                <td class="py-4 px-4 text-center">
                  <span 
                    :class="[
                      'px-3 py-1 rounded-full text-xs font-bold',
                      client.emailVerified 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-yellow-100 text-yellow-800'
                    ]"
                  >
                    {{ client.emailVerified ? 'Да' : 'Нет' }}
                  </span>
                </td>
                <td class="py-4 px-4 text-center">
                  <span 
                    :class="[
                      'px-3 py-1 rounded-full text-xs font-bold',
                      client.isBlocked 
                        ? 'bg-red-100 text-red-800' 
                        : 'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ client.isBlocked ? 'Заблокирован' : 'Активен' }}
                  </span>
                </td>
                <td class="py-4 px-4 text-center">
                  <span 
                    :class="[
                      'px-3 py-1 rounded-full text-xs font-bold',
                      client.hasMessages 
                        ? 'bg-blue-100 text-blue-800' 
                        : 'bg-gray-100 text-gray-600'
                    ]"
                  >
                    {{ client.hasMessages ? 'Есть' : 'Нет' }}
                  </span>
                </td>
                <td class="py-4 px-4 text-center text-gray-600 text-sm">
                  {{ formatDate(client.createdAt) }}
                </td>
                <td class="py-4 px-4">
                  <div class="flex gap-2 justify-center">
                    <button
                      v-if="!client.emailVerified"
                      @click="verifyEmail(client)"
                      class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold"
                      title="Подтвердить email"
                    >
                      ✓ Email
                    </button>
                    <button
                      v-if="!client.isBlocked"
                      @click="blockClient(client)"
                      class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold"
                      title="Заблокировать"
                    >
                      Заблокировать
                    </button>
                    <button
                      v-else
                      @click="unblockClient(client)"
                      class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-semibold"
                      title="Разблокировать"
                    >
                      Разблокировать
                    </button>
                    <router-link
                      :to="{ name: 'AdminChats', query: { userId: client.id } }"
                      class="px-3 py-1 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-semibold"
                      title="Открыть чат"
                    >
                      Чат
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import apiClient from '@/config/axios'

const clients = ref([])
const loading = ref(false)
const searchQuery = ref('')
const filters = ref({
  emailVerified: '',
  isBlocked: 'false', // По умолчанию показываем только незаблокированных
  hasChat: ''
})
const openDropdown = ref(null)
let searchTimeout = null

const loadClients = async () => {
  loading.value = true
  try {
    const params = {}
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    if (filters.value.emailVerified !== '') {
      params.emailVerified = filters.value.emailVerified
    }
    if (filters.value.isBlocked !== '') {
      params.isBlocked = filters.value.isBlocked
    }
    if (filters.value.hasChat !== '') {
      params.hasChat = filters.value.hasChat
    }
    
    const response = await apiClient.get('/api/admin/clients', { params })
    clients.value = response.data
  } catch (error) {
    console.error('Ошибка загрузки клиентов:', error)
    alert('Ошибка загрузки клиентов')
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    emailVerified: '',
    isBlocked: 'false', // По умолчанию показываем только незаблокированных
    hasChat: ''
  }
  searchQuery.value = ''
  loadClients()
}

const clearSearch = () => {
  searchQuery.value = ''
  loadClients()
}

const toggleDropdown = (dropdown) => {
  if (openDropdown.value === dropdown) {
    openDropdown.value = null
  } else {
    openDropdown.value = dropdown
  }
}

const selectFilter = (filterName, value) => {
  filters.value[filterName] = value
  openDropdown.value = null
  loadClients()
}

const getEmailVerifiedLabel = () => {
  if (filters.value.emailVerified === '') return 'Email: Все'
  if (filters.value.emailVerified === 'true') return 'Email: Подтвержден'
  return 'Email: Не подтвержден'
}

const getIsBlockedLabel = () => {
  if (filters.value.isBlocked === '') return 'Статус: Все'
  if (filters.value.isBlocked === 'false') return 'Статус: Активен'
  return 'Статус: Заблокирован'
}

const getHasChatLabel = () => {
  if (filters.value.hasChat === '') return 'Сообщения: Все'
  if (filters.value.hasChat === 'true') return 'Сообщения: Есть'
  return 'Сообщения: Нет'
}

// Закрываем дропдауны при клике вне их
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    openDropdown.value = null
  }
}

const debouncedSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    loadClients()
  }, 500)
}

const verifyEmail = async (client) => {
  if (!confirm(`Подтвердить email для ${client.email}?`)) {
    return
  }

  try {
    await apiClient.post(`/api/admin/clients/${client.id}/verify-email`)
    alert('Email подтвержден')
    loadClients()
  } catch (error) {
    console.error('Ошибка подтверждения email:', error)
    alert('Ошибка подтверждения email')
  }
}

const blockClient = async (client) => {
  if (!confirm(`Заблокировать пользователя ${client.email}?`)) {
    return
  }

  try {
    await apiClient.post(`/api/admin/clients/${client.id}/block`)
    alert('Пользователь заблокирован')
    loadClients()
  } catch (error) {
    console.error('Ошибка блокировки:', error)
    alert(error.response?.data?.error || 'Ошибка блокировки пользователя')
  }
}

const unblockClient = async (client) => {
  if (!confirm(`Разблокировать пользователя ${client.email}?`)) {
    return
  }

  try {
    await apiClient.post(`/api/admin/clients/${client.id}/unblock`)
    alert('Пользователь разблокирован')
    loadClients()
  } catch (error) {
    console.error('Ошибка разблокировки:', error)
    alert('Ошибка разблокировки пользователя')
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  loadClients()
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
