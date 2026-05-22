<template>
  <div class="space-y-6">
    <div class="flex items-center gap-6 pb-6 border-b border-gray-200">
      <div class="w-20 h-20 bg-[rgb(126,216,153)] rounded-full flex items-center justify-center text-white text-3xl font-bold">
        {{ userInitial }}
      </div>
      <div>
        <h2 class="text-2xl font-bold text-gray-900">{{ authStore.user?.email }}</h2>
        <p class="text-gray-500 mt-1">Пользователь</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input
          type="email"
          :value="authStore.user?.email"
          disabled
          class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-xl text-gray-700 cursor-not-allowed"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
        <input
          v-model="profileData.name"
          type="text"
          placeholder="Введите ваше имя"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[rgb(126,216,153)] focus:border-[rgb(126,216,153)]"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
        <input
          v-model="profileData.phone"
          type="tel"
          placeholder="+7 (___) ___-__-__"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[rgb(126,216,153)] focus:border-[rgb(126,216,153)]"
        />
      </div>
    </div>

    <div class="pt-4">
      <button
        @click="saveProfile"
        class="px-6 py-3 bg-[rgb(126,216,153)] text-white rounded-xl hover:bg-green-600 transition shadow-md hover:shadow-lg font-medium"
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
