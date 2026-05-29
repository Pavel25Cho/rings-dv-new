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
        class="btn btn-primary"
      >
        Сохранить изменения
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const profileData = ref({
  name: '',
  phone: ''
})

const userInitial = computed(() => {
  return authStore.user?.email?.charAt(0).toUpperCase() || '?'
})

const saveProfile = () => {
  alert('Функция сохранения профиля будет реализована')
}
</script>
