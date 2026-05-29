<template>
  <div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full glass-card-strong rounded-3xl p-10 shadow-glass-xl">
      <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center mb-6 shadow-lg">
          <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
        </div>
        <h2 class="heading-lg mb-3">
          Регистрация
        </h2>
        <p class="text-body">
          Создайте новый аккаунт для доступа к каталогу
        </p>
      </div>

      <form class="space-y-6" @submit.prevent="handleRegister">
        <div v-if="error" class="bg-red-50 border-2 border-red-300 text-red-700 px-5 py-4 rounded-xl font-semibold text-base backdrop-blur-sm">
          {{ error }}
        </div>

        <div v-if="success" class="bg-green-50 border-2 border-green-300 text-green-700 px-5 py-4 rounded-xl font-semibold text-base backdrop-blur-sm">
          Регистрация успешна! Перенаправление...
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
              minlength="6"
              class="input"
              placeholder="••••••••"
            />
            <p class="mt-2 text-sm font-medium text-gray-600">Минимум 6 символов</p>
          </div>

          <div>
            <label for="confirmPassword" class="block text-base font-bold text-gray-900 mb-2">
              Подтвердите пароль
            </label>
            <input
              id="confirmPassword"
              v-model="form.confirmPassword"
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
            {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
          </button>
        </div>

        <div class="text-center pt-4">
          <p class="text-base font-medium text-gray-700">
            Уже есть аккаунт?
            <router-link to="/login" class="font-bold text-purple-600 hover:text-purple-700 transition-colors">
              Войти
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
  password: '',
  confirmPassword: ''
})

const error = ref('')
const success = ref(false)
const loading = ref(false)

const handleRegister = async () => {
  error.value = ''
  loading.value = true

  if (form.value.password !== form.value.confirmPassword) {
    error.value = 'Пароли не совпадают'
    loading.value = false
    return
  }

  try {
    await authStore.register(form.value.email, form.value.password)

    success.value = true
    
    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (err) {
    error.value = err.response?.data?.message || 'Ошибка регистрации'
  } finally {
    loading.value = false
  }
}
</script>
