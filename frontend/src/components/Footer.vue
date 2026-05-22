<template>
  <footer class="mt-auto px-4 md:px-8 pb-4">
    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-lg p-8 mt-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h3 class="text-lg font-semibold mb-4 text-gray-900">Vlad Rings</h3>
          <p class="text-gray-600">Качественные автомобильные кольца</p>
        </div>
        <div>
          <h3 class="text-lg font-semibold mb-4 text-gray-900">Навигация</h3>
          <ul class="space-y-2">
            <li><router-link to="/" class="text-gray-600 hover:text-[rgb(126,216,153)] transition">Главная</router-link></li>
            <li><router-link to="/catalog" class="text-gray-600 hover:text-[rgb(126,216,153)] transition">Каталог</router-link></li>
          </ul>
        </div>
        <div>
          <h3 class="text-lg font-semibold mb-4 text-gray-900">Контакты</h3>
          <p class="text-gray-600">Email: info@vlad-rings.ru</p>
          <p class="text-gray-600 mt-2">ИНН: будет указан в настройках</p>
        </div>
      </div>
      <div class="border-t border-gray-200 mt-8 pt-8 text-center text-gray-600">
        <p>&copy; {{ currentYear }} vlad-rings.ru. Все права защищены.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>
