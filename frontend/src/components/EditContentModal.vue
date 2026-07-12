<template>
  <Teleport to="body">
    <Transition name="fade-backdrop">
      <div
        v-if="visible"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="closeModal"
      >
        <Transition name="scale-modal">
          <div
            v-if="visible"
            class="bg-white rounded-3xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden flex flex-col"
          >
            <div class="flex justify-between items-center p-8 border-b border-gray-200">
              <h2 class="text-3xl font-bold text-gray-900">Редактирование главной страницы</h2>
              <button
                @click="closeModal"
                class="text-gray-500 hover:text-gray-700 transition-colors"
              >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="overflow-y-auto p-8">
              <form @submit.prevent="saveContent" class="space-y-8">
                <!-- Hero Section -->
                <div class="glass-card p-6 rounded-2xl">
                  <h3 class="text-xl font-bold text-blue-700 mb-4">🎯 Главный баннер</h3>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-bold text-gray-900 mb-2">Заголовок</label>
                      <input
                        v-model="formData.hero_title"
                        type="text"
                        class="input w-full"
                        placeholder="Каталог автомобильных колец"
                        required
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-bold text-gray-900 mb-2">Описание</label>
                      <textarea
                        v-model="formData.hero_description"
                        rows="2"
                        class="input w-full"
                        placeholder="Качественные кольца для вашего автомобиля..."
                        required
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Features Section -->
                <div class="glass-card p-6 rounded-2xl">
                  <h3 class="text-xl font-bold text-blue-700 mb-4">⭐ Преимущества</h3>
                  
                  <!-- Feature 1: Quality -->
                  <div class="mb-6 p-4 bg-blue-50 rounded-xl">
                    <h4 class="text-base font-bold text-gray-900 mb-3">Блок 1</h4>
                    <div class="space-y-3">
                      <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Заголовок</label>
                        <input
                          v-model="formData.feature_quality_title"
                          type="text"
                          class="input w-full"
                          placeholder="Высокое качество"
                          required
                        />
                      </div>
                      <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Текст</label>
                        <textarea
                          v-model="formData.feature_quality_text"
                          rows="2"
                          class="input w-full"
                          placeholder="Все товары сертифицированы..."
                          required
                        ></textarea>
                      </div>
                    </div>
                  </div>

                  <!-- Feature 2: Delivery -->
                  <div class="mb-6 p-4 bg-blue-50 rounded-xl">
                    <h4 class="text-base font-bold text-gray-900 mb-3">Блок 2</h4>
                    <div class="space-y-3">
                      <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Заголовок</label>
                        <input
                          v-model="formData.feature_delivery_title"
                          type="text"
                          class="input w-full"
                          placeholder="Быстрая доставка"
                          required
                        />
                      </div>
                      <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Текст</label>
                        <textarea
                          v-model="formData.feature_delivery_text"
                          rows="2"
                          class="input w-full"
                          placeholder="Оперативная отправка заказов..."
                          required
                        ></textarea>
                      </div>
                    </div>
                  </div>

                  <!-- Feature 3: Support -->
                  <div class="p-4 bg-blue-50 rounded-xl">
                    <h4 class="text-base font-bold text-gray-900 mb-3">Блок 3</h4>
                    <div class="space-y-3">
                      <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Заголовок</label>
                        <input
                          v-model="formData.feature_support_title"
                          type="text"
                          class="input w-full"
                          placeholder="Поддержка клиентов"
                          required
                        />
                      </div>
                      <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Текст</label>
                        <textarea
                          v-model="formData.feature_support_text"
                          rows="2"
                          class="input w-full"
                          placeholder="Профессиональная консультация..."
                          required
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- About Section -->
                <div class="glass-card p-6 rounded-2xl">
                  <h3 class="text-xl font-bold text-blue-700 mb-4">ℹ️ О компании</h3>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-bold text-gray-900 mb-2">Заголовок секции</label>
                      <input
                        v-model="formData.about_title"
                        type="text"
                        class="input w-full"
                        placeholder="О компании"
                        required
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-bold text-gray-900 mb-2">Текст (переносы строк сохраняются)</label>
                      <textarea
                        v-model="formData.about_text"
                        rows="10"
                        class="input w-full"
                        placeholder="Добро пожаловать в наш каталог! Мы специализируемся на поставке качественных автомобильных колец...&#10;&#10;Наш ассортимент включает кольца для различных марок...&#10;&#10;Мы работаем как с розничными покупателями..."
                        required
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Footer Section -->
                <div class="glass-card p-6 rounded-2xl">
                  <h3 class="text-xl font-bold text-blue-700 mb-4">📧 Футер</h3>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-bold text-gray-900 mb-2">Описание компании</label>
                      <input
                        v-model="formData.footer_description"
                        type="text"
                        class="input w-full"
                        placeholder="Качественные автомобильные кольца для вашего транспорта"
                        required
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-bold text-gray-900 mb-2">Email</label>
                      <input
                        v-model="formData.footer_email"
                        type="email"
                        class="input w-full"
                        placeholder="info@vlad-rings.ru"
                        required
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-bold text-gray-900 mb-2">ИНН</label>
                      <input
                        v-model="formData.footer_inn"
                        type="text"
                        class="input w-full"
                        placeholder="будет указан"
                        required
                      />
                    </div>
                  </div>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                  <button
                    type="button"
                    @click="closeModal"
                    class="btn btn-secondary"
                  >
                    Отмена
                  </button>
                  <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="loading"
                  >
                    {{ loading ? 'Сохранение...' : 'Сохранить' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import apiClient from '@/config/axios'

const props = defineProps({
  visible: Boolean
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)

const formData = ref({
  hero_title: '',
  hero_description: '',
  feature_quality_title: '',
  feature_quality_text: '',
  feature_delivery_title: '',
  feature_delivery_text: '',
  feature_support_title: '',
  feature_support_text: '',
  about_title: '',
  about_text: '',
  footer_description: '',
  footer_email: '',
  footer_inn: ''
})

watch(() => props.visible, async (newVal) => {
  if (newVal) {
    await loadContent()
  }
})

const loadContent = async () => {
  try {
    loading.value = true
    const response = await apiClient.get('/api/admin/content')
    formData.value = { ...response.data }
  } catch (error) {
    console.error('Ошибка загрузки контента:', error)
    alert('Ошибка при загрузке контента')
  } finally {
    loading.value = false
  }
}

const saveContent = async () => {
  try {
    loading.value = true
    await apiClient.post('/api/admin/content', formData.value)
    emit('saved')
    closeModal()
  } catch (error) {
    console.error('Ошибка сохранения контента:', error)
    alert('Ошибка при сохранении контента')
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  emit('close')
}
</script>

<style scoped>
.fade-backdrop-enter-active,
.fade-backdrop-leave-active {
  transition: opacity 0.3s ease;
}

.fade-backdrop-enter-from,
.fade-backdrop-leave-to {
  opacity: 0;
}

.scale-modal-enter-active,
.scale-modal-leave-active {
  transition: all 0.3s ease;
}

.scale-modal-enter-from,
.scale-modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
