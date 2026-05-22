<template>
  <div class="px-4 md:px-8">
    <section class="max-w-7xl mx-auto mt-8">
      <div class="bg-white rounded-2xl shadow-lg p-12 md:p-16 text-center">
        <h1 class="text-5xl font-bold mb-6 text-gray-900">
          Каталог автомобильных колец
        </h1>
        <p class="text-xl mb-8 text-gray-600">
          Качественные кольца для вашего автомобиля. Широкий ассортимент, быстрая доставка.
        </p>
        <router-link 
          to="/catalog" 
          class="inline-block px-8 py-4 bg-[rgb(126,216,153)] text-white font-semibold rounded-xl hover:bg-green-600 transition transform hover:scale-105 shadow-md hover:shadow-xl"
        >
          Перейти в каталог
        </router-link>
      </div>
    </section>

    <section class="max-w-7xl mx-auto mt-8">
      <div class="bg-white rounded-2xl shadow-lg p-12 md:p-16">
        <h2 class="text-3xl font-bold text-center mb-8 text-gray-900">
          О нас
        </h2>
        <div class="max-w-3xl mx-auto text-gray-700 text-lg space-y-4">
          <p>
            Добро пожаловать в наш каталог! Мы специализируемся на поставке качественных автомобильных колец различных типов и размеров.
          </p>
          <p>
            Наш ассортимент включает кольца для различных марок автомобилей и специальной техники. Все товары сертифицированы и соответствуют международным стандартам качества.
          </p>
          <p>
            Мы работаем как с розничными покупателями, так и с корпоративными клиентами, предлагая гибкие условия сотрудничества.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
</script>
