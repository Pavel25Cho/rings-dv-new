<template>
  <header class="sticky top-4 z-50 px-4 md:px-8">
    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-lg">
      <div class="flex justify-between items-center h-16 px-6">
        <div class="flex items-center gap-6">
          <router-link 
            to="/" 
            class="text-2xl font-bold text-gray-900 flex items-center gap-2 hover-underline-effect"
          >
            <span>Vlad Rings</span>
          </router-link>
          
          <div class="h-8 w-px bg-gray-300"></div>
          
          <router-link 
            to="/catalog" 
            class="text-gray-700 font-medium hover-underline-effect"
          >
            Каталог
          </router-link>
          
          <template v-if="authStore.user?.role === 'ADMIN'">
            <div class="h-8 w-px bg-gray-300"></div>
            <router-link 
              to="/admin" 
              class="text-gray-700 font-medium hover-underline-effect"
            >
              Админ-панель
            </router-link>
          </template>
        </div>
        
        <div class="flex items-center gap-4">
          <template v-if="authStore.isAuthenticated">
            <div class="relative" ref="profileDropdown">
              <button 
                @click="toggleProfileMenu"
                class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg transition"
              >
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </button>
              
              <transition name="dropdown">
                <div 
                  v-if="isProfileMenuOpen"
                  class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100"
                >
                  <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm text-gray-500">Вы вошли как</p>
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ authStore.user?.email }}</p>
                  </div>
                  
                  <router-link 
                    to="/profile/info" 
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 transition"
                    @click="closeProfileMenu"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Личный кабинет</span>
                  </router-link>
                  
                  <router-link 
                    to="/profile/chat" 
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 transition relative"
                    @click="closeProfileMenu"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span>Чат</span>
                  </router-link>
                  
                  <button 
                    @click="handleLogout"
                    class="border-t border-gray-100 flex items-center gap-3 w-full px-4 py-3 text-red-600 hover:bg-red-50 transition"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Выйти</span>
                  </button>
                </div>
              </transition>
            </div>
          </template>
          
          <template v-else>
            <router-link 
              to="/login" 
              class="px-5 py-2 bg-[rgb(126,216,153)] text-white rounded-lg hover:bg-green-600 transition shadow-md hover:shadow-lg font-medium"
            >
              Вход
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const isProfileMenuOpen = ref(false)
const profileDropdown = ref(null)

const toggleProfileMenu = () => {
  isProfileMenuOpen.value = !isProfileMenuOpen.value
}

const closeProfileMenu = () => {
  isProfileMenuOpen.value = false
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    closeProfileMenu()
    router.push('/')
  } catch (error) {
    console.error('Ошибка выхода:', error)
  }
}

onMounted(() => {
  const handleClickOutside = (event) => {
    if (profileDropdown.value && !profileDropdown.value.contains(event.target)) {
      closeProfileMenu()
    }
  }
  
  document.addEventListener('click', handleClickOutside)
  
  onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
  })
})
</script>

<style scoped>
.hover-underline-effect {
  position: relative;
  display: inline-block;
}

.hover-underline-effect::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -2px;
  width: 0;
  height: 2px;
  background-color: rgb(126, 216, 153);
  transition: width 0.3s ease;
}

.hover-underline-effect:hover::after {
  width: 100%;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
