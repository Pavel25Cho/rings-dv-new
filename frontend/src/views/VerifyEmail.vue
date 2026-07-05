<template>
  <div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full glass-card-strong rounded-3xl p-10 shadow-glass-xl">
      <!-- Loading State -->
      <div v-if="loading" class="text-center">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center mb-6 shadow-lg">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-white border-t-transparent"></div>
        </div>
        <h2 class="heading-lg mb-3">Проверка...</h2>
        <p class="text-body">Подождите, идет подтверждение вашего email</p>
      </div>

      <!-- Success State -->
      <div v-else-if="success" class="text-center">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center mb-6 shadow-lg">
          <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="heading-lg mb-3 text-green-700">Email подтвержден!</h2>
        <p class="text-body mb-6">Ваш email успешно подтвержден. Теперь вы можете войти в систему.</p>
        <router-link 
          to="/login"
          class="btn btn-primary inline-flex items-center gap-2"
        >
          <span>Перейти к входу</span>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </router-link>
      </div>

      <!-- Error State -->
      <div v-else class="text-center">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center mb-6 shadow-lg">
          <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h2 class="heading-lg mb-3 text-red-700">Ошибка верификации</h2>
        <div class="bg-red-50 border-2 border-red-300 text-red-700 px-5 py-4 rounded-xl font-semibold text-base backdrop-blur-sm mb-6">
          {{ error }}
        </div>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <router-link 
            to="/"
            class="btn btn-secondary"
          >
            На главную
          </router-link>
          <router-link 
            to="/login"
            class="btn btn-primary"
          >
            Войти
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '@/config/axios'

const route = useRoute()

const loading = ref(true)
const success = ref(false)
const error = ref('')

onMounted(async () => {
  const token = route.query.token

  if (!token) {
    error.value = 'Токен верификации не найден'
    loading.value = false
    return
  }

  try {
    const response = await apiClient.post('/api/auth/verify-email', { token })
    
    if (response.data.success) {
      success.value = true
    } else {
      error.value = response.data.message || 'Ошибка при верификации email'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Ошибка при верификации email. Токен может быть неверным или устаревшим.'
  } finally {
    loading.value = false
  }
})
</script>
