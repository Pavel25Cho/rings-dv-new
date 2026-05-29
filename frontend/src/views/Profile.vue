<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-5xl mx-auto">
      <div class="glass-card-strong rounded-3xl p-10 mb-8">
        <h1 class="heading-xl">Личный кабинет</h1>
      </div>

      <div class="glass-card-strong rounded-3xl overflow-hidden">
        <div class="flex border-b border-gray-300/50">
          <router-link
            to="/profile/info"
            :class="[
              'flex-1 px-6 py-5 text-center font-bold transition-all text-base',
              isActiveTab('/profile/info')
                ? 'text-purple-600 border-b-4 border-purple-600 bg-white/40' 
                : 'text-gray-700 hover:text-gray-900 hover:bg-white/30'
            ]"
          >
            <span class="flex items-center justify-center gap-3">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Личная информация
            </span>
          </router-link>
          
          <router-link
            to="/profile/chat"
            :class="[
              'flex-1 px-6 py-5 text-center font-bold transition-all text-base',
              isActiveTab('/profile/chat')
                ? 'text-purple-600 border-b-4 border-purple-600 bg-white/40' 
                : 'text-gray-700 hover:text-gray-900 hover:bg-white/30'
            ]"
          >
            <span class="flex items-center justify-center gap-3">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              Чат
              <span 
                v-if="unreadCount > 0"
                class="ml-1 bg-gradient-to-br from-purple-500 to-indigo-600 text-white text-sm font-bold rounded-full px-2.5 py-1"
              >
                {{ unreadCount }}
              </span>
            </span>
          </router-link>
        </div>

        <div class="p-10">
          <router-view />
        </div>
      </div>

      <div class="glass-card rounded-2xl p-6 mt-8">
        <button
          @click="handleLogout"
          class="flex items-center gap-3 w-full px-6 py-4 text-red-600 hover:bg-red-50 rounded-xl transition-all font-bold text-base"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>Выйти из аккаунта</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const unreadCount = ref(0)

const isActiveTab = (path) => {
  if (path === '/profile/info') {
    return route.path === '/profile' || route.path === '/profile/' || route.path === '/profile/info'
  }
  return route.path === path
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/')
  } catch (error) {
    console.error('Ошибка выхода:', error)
  }
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
  }
  
  if (route.path === '/profile' || route.path === '/profile/') {
    router.replace('/profile/info')
  }
})
</script>
