<template>
  <div class="px-4 md:px-8 pb-16">
    <section class="max-w-4xl mx-auto mt-12">
      <div class="glass-card rounded-3xl p-8 md:p-12">
        <h1 class="heading-lg text-center mb-8">
          Политика конфиденциальности
        </h1>
        
        <div class="space-y-6 text-sm">
          <p class="text-center text-gray-600 italic mb-8">
            Страница находится в разработке
          </p>
          
          <section class="space-y-3">
            <h2 class="text-lg font-bold text-gray-900">1. Общие положения</h2>
            <p class="text-gray-700">
              Настоящая Политика конфиденциальности определяет порядок обработки и защиты персональных данных пользователей сайта vlad-rings.ru.
            </p>
            <p class="text-gray-700">
              Используя наш сайт, вы соглашаетесь с условиями данной Политики конфиденциальности.
            </p>
          </section>

          <section class="space-y-3">
            <h2 class="text-lg font-bold text-gray-900">2. Сбор информации</h2>
            <p class="text-gray-700">
              Мы собираем только ту информацию, которую вы предоставляете добровольно при регистрации на сайте и оформлении заказов:
            </p>
            <ul class="list-disc list-inside space-y-1.5 ml-4 text-gray-700">
              <li>ФИО</li>
              <li>Контактный телефон</li>
              <li>Адрес электронной почты</li>
              <li>Адрес доставки</li>
            </ul>
          </section>

          <section class="space-y-3">
            <h2 class="text-lg font-bold text-gray-900">3. Использование информации</h2>
            <p class="text-gray-700">
              Собранная информация используется исключительно для:
            </p>
            <ul class="list-disc list-inside space-y-1.5 ml-4 text-gray-700">
              <li>Обработки и выполнения ваших заказов</li>
              <li>Связи с вами по вопросам заказа</li>
              <li>Улучшения качества обслуживания</li>
              <li>Информирования о новых товарах и специальных предложениях</li>
            </ul>
          </section>

          <section class="space-y-3">
            <h2 class="text-lg font-bold text-gray-900">4. Защита информации</h2>
            <p class="text-gray-700">
              Мы применяем современные технологии для защиты ваших персональных данных от несанкционированного доступа, изменения или уничтожения.
            </p>
          </section>

          <section class="space-y-3">
            <h2 class="text-lg font-bold text-gray-900">5. Контактная информация</h2>
            <p class="text-gray-700">
              По всем вопросам, касающимся обработки персональных данных, вы можете обратиться к нам по электронной почте: 
              <a href="mailto:info@vlad-rings.ru" class="text-purple-600 hover:text-purple-700 font-semibold">info@vlad-rings.ru</a>
            </p>
          </section>

          <div class="border-t border-gray-300/50 mt-8 pt-6">
            <p class="text-sm text-gray-600 text-center">
              Последнее обновление: {{ currentDate }}
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const currentDate = computed(() => {
  const now = new Date()
  return now.toLocaleDateString('ru-RU', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
})
</script>
