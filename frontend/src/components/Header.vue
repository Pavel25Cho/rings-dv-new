<template>
  <header class="sticky top-0 z-50 px-4 md:px-8 mt-4 md:mt-8">
    <div class="max-w-7xl mx-auto glass-card rounded-2xl transition-smooth">
      <div class="flex justify-between items-center h-20 px-8">
        <div class="flex items-center gap-8">
          <router-link 
            to="/" 
            class="flex items-center gap-3 group"
          >
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <span class="text-2xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors">
              Vlad Rings
            </span>
          </router-link>
          
          <div class="h-10 w-px bg-gradient-to-b from-transparent via-gray-300 to-transparent"></div>
          
          <nav class="flex items-center gap-1">
            <router-link 
              to="/catalog" 
              class="nav-link"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
              <span>Каталог</span>
            </router-link>
            
            <template v-if="authStore.user?.role === 'ADMIN'">
              <router-link 
                to="/admin" 
                class="nav-link"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Админ-панель</span>
              </router-link>
            </template>
          </nav>
        </div>
        
        <div class="flex items-center gap-3">
          <template v-if="authStore.isAuthenticated">
            <div class="relative" ref="cartDropdown">
              <button 
                @click="toggleCartMenu"
                class="relative flex items-center gap-2 px-4 py-2.5 hover:bg-white/40 rounded-xl transition-all duration-300 backdrop-blur-sm"
              >
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-md relative">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                  <span 
                    v-if="cartStore.cartCount > 0"
                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
                  >
                    {{ cartStore.cartCount > 9 ? '9+' : cartStore.cartCount }}
                  </span>
                </div>
              </button>
              
              <CartDropdown
                :visible="isCartMenuOpen"
                @close="closeCartMenu"
              />
            </div>
            
            <div class="relative" ref="profileDropdown">
              <button 
                @click="toggleProfileMenu"
                class="flex items-center gap-3 px-4 py-2.5 hover:bg-white/40 rounded-xl transition-all duration-300 backdrop-blur-sm"
              >
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </button>
              
              <transition name="dropdown">
                <div 
                  v-if="isProfileMenuOpen"
                  class="absolute right-0 mt-3 w-72 glass-card-strongest rounded-2xl overflow-hidden"
                >
                  <div class="px-5 py-4 border-b border-gray-200/50">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Аккаунт</p>
                    <p class="text-base font-bold text-gray-900 truncate mt-1">{{ authStore.user?.email }}</p>
                  </div>
                  
                  <div class="p-2">
                    <router-link 
                      to="/profile/info" 
                      class="menu-item"
                      @click="closeProfileMenu"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <span class="font-semibold">Личный кабинет</span>
                    </router-link>
                    
                    <router-link 
                      to="/profile/chat" 
                      class="menu-item"
                      @click="closeProfileMenu"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                      </svg>
                      <span class="font-semibold">Чат</span>
                    </router-link>
                  </div>
                  
                  <div class="border-t border-gray-200/50 p-2">
                    <button 
                      @click="handleLogout"
                      class="menu-item text-red-600 hover:bg-red-50"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      <span class="font-semibold">Выйти</span>
                    </button>
                  </div>
                </div>
              </transition>
            </div>
          </template>
          
          <template v-else>
            <router-link 
              to="/login" 
              class="btn btn-primary"
            >
              Войти
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
import { useCartStore } from '@/stores/cart'
import CartDropdown from './CartDropdown.vue'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

const isProfileMenuOpen = ref(false)
const isCartMenuOpen = ref(false)
const profileDropdown = ref(null)
const cartDropdown = ref(null)

const toggleProfileMenu = () => {
  isProfileMenuOpen.value = !isProfileMenuOpen.value
  if (isProfileMenuOpen.value) {
    isCartMenuOpen.value = false
  }
}

const closeProfileMenu = () => {
  isProfileMenuOpen.value = false
}

const toggleCartMenu = () => {
  isCartMenuOpen.value = !isCartMenuOpen.value
  if (isCartMenuOpen.value) {
    isProfileMenuOpen.value = false
    cartStore.fetchCart()
  }
}

const closeCartMenu = () => {
  isCartMenuOpen.value = false
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
    if (cartDropdown.value && !cartDropdown.value.contains(event.target)) {
      closeCartMenu()
    }
  }
  
  document.addEventListener('click', handleClickOutside)
  
  // Загружаем корзину при монтировании компонента
  if (authStore.isAuthenticated) {
    cartStore.fetchCart()
  }
  
  onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
  })
})
</script>

<style scoped>
.nav-link {
  @apply flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-gray-700 hover:text-gray-900 hover:bg-white/50 transition-all duration-300 text-base;
}

.nav-link.router-link-active {
  @apply bg-white/70 text-purple-600;
}

.menu-item {
  @apply flex items-center gap-3 w-full px-4 py-3 text-gray-700 hover:bg-white/60 transition-all duration-200 rounded-xl text-base;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.dropdown-enter-to,
.dropdown-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}
</style>
