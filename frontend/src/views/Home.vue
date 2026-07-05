<template>
  <div class="px-4 md:px-8 pb-16">
    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto mt-12 mb-16">
      <div class="glass-card-strong rounded-3xl p-16 md:p-24 text-center relative overflow-hidden">
        <!-- Decorative gradient orbs -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-400/20 rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl -z-10"></div>
        
        <div class="relative z-10">
          <h1 class="heading-hero mb-8">
            {{ content.hero_title }}
          </h1>
          <p class="text-body max-w-3xl mx-auto mb-12 text-xl whitespace-pre-line">
            {{ content.hero_description }}
          </p>
          <div class="flex items-center justify-center gap-4 flex-wrap">
            <router-link 
              to="/catalog" 
              class="btn btn-primary inline-flex items-center gap-2"
            >
              <span>Перейти в каталог</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="max-w-7xl mx-auto mb-16">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card rounded-2xl p-8 hover:shadow-glass-lg transition-smooth">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center mb-6 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h3 class="heading-md mb-4">{{ content.feature_quality_title }}</h3>
          <p class="text-body whitespace-pre-line">
            {{ content.feature_quality_text }}
          </p>
        </div>

        <div class="glass-card rounded-2xl p-8 hover:shadow-glass-lg transition-smooth">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center mb-6 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h3 class="heading-md mb-4">{{ content.feature_delivery_title }}</h3>
          <p class="text-body whitespace-pre-line">
            {{ content.feature_delivery_text }}
          </p>
        </div>

        <div class="glass-card rounded-2xl p-8 hover:shadow-glass-lg transition-smooth">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center mb-6 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="heading-md mb-4">{{ content.feature_support_title }}</h3>
          <p class="text-body whitespace-pre-line">
            {{ content.feature_support_text }}
          </p>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section class="max-w-7xl mx-auto">
      <div class="glass-card rounded-3xl p-12 md:p-16">
        <h2 class="heading-lg text-center mb-10">
          {{ content.about_title }}
        </h2>
        <div class="max-w-3xl mx-auto">
          <p class="text-body whitespace-pre-line">{{ content.about_text }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/config/axios'

const content = ref({
  hero_title: 'Каталог автомобильных колец',
  hero_description: 'Качественные кольца для вашего автомобиля. Широкий ассортимент, быстрая доставка по всей стране.',
  feature_quality_title: 'Высокое качество',
  feature_quality_text: 'Все товары сертифицированы и соответствуют международным стандартам качества',
  feature_delivery_title: 'Быстрая доставка',
  feature_delivery_text: 'Оперативная отправка заказов во все регионы страны',
  feature_support_title: 'Поддержка клиентов',
  feature_support_text: 'Профессиональная консультация и помощь в выборе товара',
  about_title: 'О компании',
  about_text: 'Добро пожаловать в наш каталог! Мы специализируемся на поставке качественных автомобильных колец различных типов и размеров.\n\nНаш ассортимент включает кольца для различных марок автомобилей и специальной техники. Все товары сертифицированы и соответствуют международным стандартам качества.\n\nМы работаем как с розничными покупателями, так и с корпоративными клиентами, предлагая гибкие условия сотрудничества и индивидуальный подход к каждому заказу.'
})

onMounted(async () => {
  try {
    const response = await apiClient.get('/api/catalog/content')
    content.value = { ...content.value, ...response.data }
  } catch (error) {
    console.error('Ошибка загрузки контента:', error)
  }
})
</script>
