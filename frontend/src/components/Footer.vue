<template>
  <footer class="mt-auto px-4 md:px-8 pb-6">
    <div class="max-w-7xl mx-auto glass-card rounded-3xl p-10 mt-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Vlad Rings</h3>
          </div>
          <p class="text-base font-medium text-gray-700">Качественные автомобильные кольца для вашего транспорта</p>
        </div>
        
        <div>
          <h3 class="text-lg font-bold mb-4 text-gray-900">Навигация</h3>
          <ul class="space-y-3">
            <li>
              <router-link to="/" class="text-base font-medium text-gray-700 hover:text-purple-600 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                Главная
              </router-link>
            </li>
            <li>
              <router-link to="/catalog" class="text-base font-medium text-gray-700 hover:text-purple-600 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                Каталог
              </router-link>
            </li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-lg font-bold mb-4 text-gray-900">Контакты</h3>
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <p class="text-base font-medium text-gray-700">info@vlad-rings.ru</p>
            </div>
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-base font-medium text-gray-700">ИНН: будет указан</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="border-t border-gray-300/50 mt-8 pt-8 text-center">
        <p class="text-base font-semibold text-gray-700">&copy; {{ currentYear }} vlad-rings.ru. Все права защищены.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>
