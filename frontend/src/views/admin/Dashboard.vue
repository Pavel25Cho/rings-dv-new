<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Админ панель</h1>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center hover:shadow-xl transition transform hover:-translate-y-1">
          <div class="text-5xl mr-5">🔗</div>
          <div class="flex-1">
            <div class="text-3xl font-bold text-gray-900">{{ stats.groupsCount }}</div>
            <div class="text-sm text-gray-500 uppercase tracking-wide mt-1">Групп колец</div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center hover:shadow-xl transition transform hover:-translate-y-1">
          <div class="text-5xl mr-5">⭕</div>
          <div class="flex-1">
            <div class="text-3xl font-bold text-gray-900">{{ stats.ringsCount }}</div>
            <div class="text-sm text-gray-500 uppercase tracking-wide mt-1">Колец</div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center hover:shadow-xl transition transform hover:-translate-y-1">
          <div class="text-5xl mr-5">👥</div>
          <div class="flex-1">
            <div class="text-3xl font-bold text-gray-900">{{ stats.clientsCount }}</div>
            <div class="text-sm text-gray-500 uppercase tracking-wide mt-1">Клиентов</div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center hover:shadow-xl transition transform hover:-translate-y-1">
          <div class="text-5xl mr-5">💬</div>
          <div class="flex-1">
            <div class="text-3xl font-bold text-gray-900">{{ stats.chatsCount }}</div>
            <div class="text-sm text-gray-500 uppercase tracking-wide mt-1">Активных чатов</div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Быстрые действия</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <router-link 
            to="/admin/groups/create" 
            class="flex items-center p-5 bg-gray-50 rounded-xl hover:bg-[rgb(126,216,153)] hover:text-white transition transform hover:-translate-y-1 font-medium"
          >
            <span class="text-2xl mr-3">➕</span>
            <span>Добавить группу</span>
          </router-link>
          <router-link 
            to="/admin/rings/create" 
            class="flex items-center p-5 bg-gray-50 rounded-xl hover:bg-[rgb(126,216,153)] hover:text-white transition transform hover:-translate-y-1 font-medium"
          >
            <span class="text-2xl mr-3">➕</span>
            <span>Добавить кольцо</span>
          </router-link>
          <router-link 
            to="/admin/import" 
            class="flex items-center p-5 bg-gray-50 rounded-xl hover:bg-[rgb(126,216,153)] hover:text-white transition transform hover:-translate-y-1 font-medium"
          >
            <span class="text-2xl mr-3">📥</span>
            <span>Импорт Excel</span>
          </router-link>
          <router-link 
            to="/admin/chats" 
            class="flex items-center p-5 bg-gray-50 rounded-xl hover:bg-[rgb(126,216,153)] hover:text-white transition transform hover:-translate-y-1 font-medium"
          >
            <span class="text-2xl mr-3">💬</span>
            <span>Открыть чаты</span>
          </router-link>
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
  groupsCount: 0,
  ringsCount: 0,
  clientsCount: 0,
  chatsCount: 0
})

const goToHome = () => {
  router.push('/')
}

onMounted(async () => {
  try {
    const responses = await Promise.allSettled([
      apiClient.get('/api/admin/groups'),
      apiClient.get('/api/admin/rings'),
      apiClient.get('/api/admin/clients'),
      apiClient.get('/api/chats')
    ])
    
    stats.value = {
      groupsCount: responses[0].status === 'fulfilled' ? (responses[0].value.data?.length || 0) : 0,
      ringsCount: responses[1].status === 'fulfilled' ? (responses[1].value.data?.length || 0) : 0,
      clientsCount: responses[2].status === 'fulfilled' ? (responses[2].value.data?.length || 0) : 0,
      chatsCount: responses[3].status === 'fulfilled' ? (responses[3].value.data?.length || 0) : 0
    }
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error)
  }
})
</script>
