<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="glass-card rounded-3xl p-10 mb-8">
        <h1 class="heading-xl">Админ панель</h1>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div
          @click="goToGroups"
          class="glass-card rounded-2xl py-2 px-4 flex items-center hover:shadow-glass-lg transition-smooth cursor-pointer"
        >
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mr-5 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900">{{ stats.groupsWithStock }}</div>
            <div class="text-sm font-bold text-gray-600 uppercase tracking-wide mt-1">Групп в наличии</div>
            <div class="text-xs text-gray-500 mt-1">из {{ stats.groupsTotal }}</div>
          </div>
        </div>
        
        <div
          @click="goToRings"
          class="glass-card rounded-2xl py-2 px-4 flex items-center hover:shadow-glass-lg transition-smooth cursor-pointer"
        >
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center mr-5 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900">{{ stats.ringsWithStock }}</div>
            <div class="text-sm font-bold text-gray-600 uppercase tracking-wide mt-1">Колец в наличии</div>
            <div class="text-xs text-gray-500 mt-1">из {{ stats.ringsTotal }}</div>
          </div>
        </div>
        
        <div class="glass-card rounded-2xl py-2 px-4 flex items-center hover:shadow-glass-lg transition-smooth">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center mr-5 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900">{{ stats.clientsCount }}</div>
            <div class="text-sm font-bold text-gray-600 uppercase tracking-wide mt-1">Клиентов</div>
          </div>
        </div>
        
        <div class="glass-card rounded-2xl py-2 px-4 flex items-center hover:shadow-glass-lg transition-smooth">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center mr-5 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900">{{ stats.chatsCount }}</div>
            <div class="text-sm font-bold text-gray-600 uppercase tracking-wide mt-1">Активных чатов</div>
          </div>
        </div>
      </div>
      
      <div class="glass-card-strong rounded-3xl p-10">
        <h2 class="heading-lg mb-8">Быстрые действия</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <router-link 
            to="/admin/groups/create" 
            class="flex items-center p-4 glass-card rounded-2xl hover:bg-white/70 hover:shadow-glass-lg transition-smooth font-bold text-base text-gray-900"
          >
            <svg class="w-8 h-8 mr-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Добавить группу</span>
          </router-link>
          <router-link 
            to="/admin/rings/create" 
            class="flex items-center p-4 glass-card rounded-2xl hover:bg-white/70 hover:shadow-glass-lg transition-smooth font-bold text-base text-gray-900"
          >
            <svg class="w-8 h-8 mr-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Добавить кольцо</span>
          </router-link>
          <router-link 
            to="/admin/import" 
            class="flex items-center p-4 glass-card rounded-2xl hover:bg-white/70 hover:shadow-glass-lg transition-smooth font-bold text-base text-gray-900"
          >
            <svg class="w-8 h-8 mr-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span>Импорт данных</span>
          </router-link>
          <router-link 
            to="/admin/chats" 
            class="flex items-center p-4 glass-card rounded-2xl hover:bg-white/70 hover:shadow-glass-lg transition-smooth font-bold text-base text-gray-900"
          >
            <svg class="w-8 h-8 mr-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span>Открыть чаты</span>
          </router-link>
          
          <button
            @click="confirmDeleteCatalog"
            class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-bold"
          >
            Удалить весь каталог
          </button>
        </div>
        
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/config/axios'

const router = useRouter()

const stats = ref({
  groupsTotal: 0,
  groupsWithStock: 0,
  ringsTotal: 0,
  ringsWithStock: 0,
  clientsCount: 0,
  chatsCount: 0
})

const goToHome = () => {
  router.push('/')
}

const goToGroups = () => {
  router.push({ name: 'AdminGroups' })
}

const goToRings = () => {
  router.push({ name: 'AdminRings' })
}

const confirmDeleteCatalog = async () => {
  const confirmed = window.confirm(
    'ВНИМАНИЕ! Вы действительно хотите удалить ВСЕ группы и кольца из каталога? Это действие НЕВОЗМОЖНО отменить!'
  )
  
  if (!confirmed) return
  
  const doubleConfirm = window.confirm(
    'Это последнее предупреждение! После удаления восстановить данные будет невозможно. Продолжить?'
  )
  
  if (!doubleConfirm) return
  
  try {
    await apiClient.delete('/api/admin/catalog/delete-all')
    alert('Каталог успешно удален')
    
    // Обновляем статистику
    stats.value.groupsTotal = 0
    stats.value.groupsWithStock = 0
    stats.value.ringsTotal = 0
    stats.value.ringsWithStock = 0
  } catch (error) {
    console.error('Ошибка удаления каталога:', error)
    alert('Ошибка при удалении каталога')
  }
}

onMounted(async () => {
  try {
    const responses = await Promise.allSettled([
      apiClient.get('/api/admin/stats'),
      apiClient.get('/api/admin/clients'),
      apiClient.get('/api/chats')
    ])
    
    if (responses[0].status === 'fulfilled') {
      stats.value = {
        ...stats.value,
        ...responses[0].value.data
      }
    }
    
    stats.value.clientsCount = responses[1].status === 'fulfilled' ? (responses[1].value.data?.length || 0) : 0
    stats.value.chatsCount = responses[2].status === 'fulfilled' ? (responses[2].value.data?.length || 0) : 0
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error)
  }
})
</script>
