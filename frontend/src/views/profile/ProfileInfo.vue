<template>
  <div class="space-y-8">
    <div class="flex items-center gap-6 pb-8 border-b border-gray-300/50">
      <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white text-4xl font-bold shadow-lg">
        {{ userInitial }}
      </div>
      <div>
        <h2 class="heading-md mb-2">{{ authStore.user?.email }}</h2>
        <p class="text-body">Пользователь</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-base font-bold text-gray-900 mb-2">Email</label>
        <input
          type="email"
          :value="authStore.user?.email"
          disabled
          class="w-full px-5 py-4 bg-white/60 border-0 rounded-xl text-gray-700 cursor-not-allowed font-medium text-base backdrop-blur-sm"
        />
      </div>

      <div>
        <label class="block text-base font-bold text-gray-900 mb-2">Имя</label>
        <input
          v-model="profileData.name"
          type="text"
          placeholder="Введите ваше имя"
          class="input"
        />
      </div>

      <div>
        <label class="block text-base font-bold text-gray-900 mb-2">Телефон</label>
        <input
          v-model="profileData.phone"
          type="tel"
          placeholder="+7 (___) ___-__-__"
          class="input"
        />
      </div>
    </div>

    <div class="pt-4">
      <button
        @click="saveProfile"
        :disabled="saving"
        class="btn btn-primary"
      >
        {{ saving ? 'Сохранение...' : 'Сохранить изменения' }}
      </button>
      
      <div v-if="message" :class="[
        'mt-4 p-4 rounded-xl',
        messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
      ]">
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const profileData = ref({
  name: '',
  phone: ''
})

const saving = ref(false)
const message = ref('')
const messageType = ref('') // 'success' или 'error'

// Инициализируем данные профиля при загрузке
watch(() => authStore.user, (user) => {
  if (user) {
    profileData.value.name = user.name || ''
    profileData.value.phone = user.phone || ''
  }
}, { immediate: true })

const userInitial = computed(() => {
  if (authStore.user?.name) {
    return authStore.user.name.charAt(0).toUpperCase()
  }
  return authStore.user?.email?.charAt(0).toUpperCase() || '?'
})

const saveProfile = async () => {
  saving.value = true
  message.value = ''
  
  try {
    await authStore.updateProfile({
      name: profileData.value.name,
      phone: profileData.value.phone
    })
    
    message.value = 'Профиль успешно обновлен'
    messageType.value = 'success'
    
    setTimeout(() => {
      message.value = ''
    }, 3000)
  } catch (error) {
    message.value = error.response?.data?.message || 'Ошибка при сохранении профиля'
    messageType.value = 'error'
  } finally {
    saving.value = false
  }
}
</script>
