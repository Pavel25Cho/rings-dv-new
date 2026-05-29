<template>
  <div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full glass-card-strong rounded-3xl p-10 shadow-glass-xl">
      <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center mb-6 shadow-lg">
          <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
        </div>
        <h2 class="heading-lg mb-3">
          Вход в систему
        </h2>
        <p class="text-body">
          Войдите в свой аккаунт для продолжения
        </p>
      </div>

      <form class="space-y-6" @submit.prevent="handleLogin">
        <div v-if="error" class="bg-red-50 border-2 border-red-300 text-red-700 px-5 py-4 rounded-xl font-semibold text-base backdrop-blur-sm">
          {{ error }}
        </div>

        <div class="space-y-5">
          <div>
            <label for="email" class="block text-base font-bold text-gray-900 mb-2">
              Email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="input"
              placeholder="your@email.com"
            />
          </div>

          <div>
            <label for="password" class="block text-base font-bold text-gray-900 mb-2">
              Пароль
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="input"
              placeholder="••••••••"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="btn btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Вход...' : 'Войти' }}
          </button>
        </div>

        <div class="text-center pt-4">
          <p class="text-base font-medium text-gray-700">
            Нет аккаунта?
            <router-link to="/register" class="font-bold text-purple-600 hover:text-purple-700 transition-colors">
              Зарегистрироваться
            </router-link>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: ''
})

const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    const response = await authStore.login(form.value.email, form.value.password)
    
    if (response.user.role === 'ADMIN') {
      router.push('/admin')
    } else {
      router.push('/catalog')
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Ошибка входа'
  } finally {
    loading.value = false
  }
}
</script>
