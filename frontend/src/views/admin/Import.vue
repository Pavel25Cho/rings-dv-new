<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <div class="glass-card rounded-3xl p-10 mb-8">
        <div class="flex items-center justify-between mb-8">
          <h1 class="heading-xl">Импорт данных из Excel</h1>
          <router-link 
            to="/admin"
            class="flex items-center gap-2 text-gray-600 hover:text-purple-600 transition-colors font-bold"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Назад</span>
          </router-link>
        </div>

        <!-- Вкладки -->
        <div class="flex gap-4 mb-8 border-b border-gray-200">
          <button
            @click="activeTab = 'rings'"
            class="px-6 py-3 font-bold text-base transition-colors border-b-2"
            :class="activeTab === 'rings' 
              ? 'text-purple-600 border-purple-600' 
              : 'text-gray-500 border-transparent hover:text-gray-700'"
          >
            Импорт товаров
          </button>
          <button
            @click="activeTab = 'prices'"
            class="px-6 py-3 font-bold text-base transition-colors border-b-2"
            :class="activeTab === 'prices' 
              ? 'text-purple-600 border-purple-600' 
              : 'text-gray-500 border-transparent hover:text-gray-700'"
          >
            Импорт цен и количества
          </button>
        </div>

        <!-- Вкладка "Импорт товаров" -->
        <div v-if="activeTab === 'rings'">
          <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-xl mb-8">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-base font-bold text-blue-800 mb-2">Инструкция по импорту товаров</p>
                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                  <li>Загрузите файл Excel (.xlsx или .xls)</li>
                  <li>Файл должен содержать группы на странице 3 и размеры на последующих страницах</li>
                  <li>Импорт автоматически обновит существующие данные и добавит новые</li>
                </ul>
              </div>
            </div>
          </div>

          <div 
            class="glass-card rounded-3xl p-12 text-center border-2 border-dashed transition-all"
            :class="isDragging ? 'border-purple-500 bg-purple-50/50' : 'border-gray-300'"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleFileDrop"
          >
            <input
              ref="fileInput"
              type="file"
              accept=".xlsx,.xls"
              class="hidden"
              @change="handleFileSelect"
            />

            <template v-if="!uploading && !result">
              <svg class="w-20 h-20 mx-auto mb-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              
              <h3 class="heading-md mb-3">Перетащите файл сюда</h3>
              <p class="text-body mb-8">или</p>
              
              <button
                @click="$refs.fileInput.click()"
                class="btn-primary inline-flex items-center gap-3 px-8 py-4 text-base font-bold"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Выбрать файл
              </button>
              
              <p class="text-sm text-gray-500 mt-6">Поддерживаемые форматы: .xlsx, .xls</p>
              
              <div v-if="selectedFile" class="mt-6 p-4 glass-card rounded-xl inline-flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-base font-bold text-gray-900">{{ selectedFile.name }}</span>
                <button @click="selectedFile = null" class="text-red-500 hover:text-red-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <button
                v-if="selectedFile"
                @click="uploadFile"
                class="btn-primary mt-6 px-10 py-4 text-base font-bold"
              >
                Начать импорт
              </button>
            </template>

            <template v-if="uploading">
              <div class="inline-block animate-spin rounded-full h-20 w-20 border-4 border-gray-300 border-t-purple-600 mb-6"></div>
              <h3 class="heading-md mb-3">Импорт данных...</h3>
              <p class="text-body">Пожалуйста, подождите</p>
            </template>

            <template v-if="result && !uploading">
              <div v-if="result.success">
                <svg class="w-20 h-20 mx-auto mb-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                
                <h3 class="heading-md mb-6 text-green-600">Импорт успешно завершен!</h3>
                
                <div class="glass-card-strong rounded-2xl p-8 text-left mb-8">
                  <h4 class="font-bold text-lg mb-4 text-gray-900">Результаты импорта:</h4>
                  <div class="space-y-3">
                    <div class="flex justify-between items-center">
                      <span class="text-base text-gray-700">Создано групп:</span>
                      <span class="font-bold text-lg text-purple-600">{{ result.stats.groups_created }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-base text-gray-700">Обновлено групп:</span>
                      <span class="font-bold text-lg text-blue-600">{{ result.stats.groups_updated }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-base text-gray-700">Создано колец:</span>
                      <span class="font-bold text-lg text-purple-600">{{ result.stats.rings_created }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-base text-gray-700">Обновлено колец:</span>
                      <span class="font-bold text-lg text-blue-600">{{ result.stats.rings_updated }}</span>
                    </div>
                  </div>

                  <div v-if="result.stats.errors && result.stats.errors.length > 0" class="mt-6 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                    <h5 class="font-bold text-sm text-yellow-800 mb-2">Предупреждения:</h5>
                    <ul class="text-sm text-yellow-700 space-y-1">
                      <li v-for="(error, index) in result.stats.errors" :key="index">{{ error }}</li>
                    </ul>
                  </div>
                </div>

                <button
                  @click="resetUpload"
                  class="btn-primary px-8 py-4 text-base font-bold"
                >
                  Загрузить еще один файл
                </button>
              </div>

              <div v-else class="text-center">
                <svg class="w-20 h-20 mx-auto mb-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                
                <h3 class="heading-md mb-4 text-red-600">Ошибка импорта</h3>
                <p class="text-body mb-8">{{ result.message }}</p>
                
                <button
                  @click="resetUpload"
                  class="btn-primary px-8 py-4 text-base font-bold"
                >
                  Попробовать снова
                </button>
              </div>
            </template>
          </div>
        </div>

        <!-- Вкладка "Импорт цен и количества" -->
        <div v-if="activeTab === 'prices'">
          <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-xl mb-8">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-base font-bold text-green-800 mb-2">Инструкция по импорту цен и количества</p>
                <ul class="text-sm text-green-700 space-y-1 list-disc list-inside">
                  <li>Загрузите файл Excel (.xlsx или .xls)</li>
                  <li>Колонка A: Тип кольца, Колонка B: Номер</li>
                  <li>Колонка F: Количество, Колонка M: Цена</li>
                  <li>Первая строка (заголовок) будет пропущена</li>
                </ul>
              </div>
            </div>
          </div>

          <div 
            class="glass-card rounded-3xl p-12 text-center border-2 border-dashed transition-all"
            :class="isDraggingPrices ? 'border-green-500 bg-green-50/50' : 'border-gray-300'"
            @dragover.prevent="isDraggingPrices = true"
            @dragleave.prevent="isDraggingPrices = false"
            @drop.prevent="handleFileDropPrices"
          >
            <input
              ref="fileInputPrices"
              type="file"
              accept=".xlsx,.xls"
              class="hidden"
              @change="handleFileSelectPrices"
            />

            <template v-if="!uploadingPrices && !resultPrices">
              <svg class="w-20 h-20 mx-auto mb-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              
              <h3 class="heading-md mb-3">Перетащите файл сюда</h3>
              <p class="text-body mb-8">или</p>
              
              <button
                @click="$refs.fileInputPrices.click()"
                class="btn-primary inline-flex items-center gap-3 px-8 py-4 text-base font-bold"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Выбрать файл
              </button>
              
              <p class="text-sm text-gray-500 mt-6">Поддерживаемые форматы: .xlsx, .xls</p>
              
              <div v-if="selectedFilePrices" class="mt-6 p-4 glass-card rounded-xl inline-flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-base font-bold text-gray-900">{{ selectedFilePrices.name }}</span>
                <button @click="selectedFilePrices = null" class="text-red-500 hover:text-red-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <button
                v-if="selectedFilePrices"
                @click="uploadFilePrices"
                class="btn-primary mt-6 px-10 py-4 text-base font-bold"
              >
                Начать импорт
              </button>
            </template>

            <template v-if="uploadingPrices">
              <div class="inline-block animate-spin rounded-full h-20 w-20 border-4 border-gray-300 border-t-green-600 mb-6"></div>
              <h3 class="heading-md mb-3">Импорт цен и количества...</h3>
              <p class="text-body">Пожалуйста, подождите</p>
            </template>

            <template v-if="resultPrices && !uploadingPrices">
              <div v-if="resultPrices.success">
                <svg class="w-20 h-20 mx-auto mb-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                
                <h3 class="heading-md mb-6 text-green-600">Импорт успешно завершен!</h3>
                
                <div class="glass-card-strong rounded-2xl p-8 text-left mb-8">
                  <h4 class="font-bold text-lg mb-4 text-gray-900">Результаты импорта:</h4>
                  <div class="space-y-3">
                    <div class="flex justify-between items-center">
                      <span class="text-base text-gray-700">Обработано строк:</span>
                      <span class="font-bold text-lg text-blue-600">{{ resultPrices.stats.rows_processed }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-base text-gray-700">Обновлено колец:</span>
                      <span class="font-bold text-lg text-green-600">{{ resultPrices.stats.rings_updated }}</span>
                    </div>
                    <div v-if="resultPrices.stats.rings_not_found > 0" class="flex justify-between items-center">
                      <span class="text-base text-gray-700">Не найдено:</span>
                      <span class="font-bold text-lg text-yellow-600">{{ resultPrices.stats.rings_not_found }}</span>
                    </div>
                  </div>

                  <!-- Список не найденных колец -->
                  <div v-if="resultPrices.stats.rings_not_found_list && resultPrices.stats.rings_not_found_list.length > 0" 
                       class="mt-6 p-4 bg-orange-50 rounded-xl border border-orange-200">
                    <h5 class="font-bold text-sm text-orange-800 mb-3">Не найденные кольца ({{ resultPrices.stats.rings_not_found }}):</h5>
                    <div class="max-h-64 overflow-y-auto pr-2">
                      <table class="w-full text-xs">
                        <thead class="sticky top-0 bg-orange-50">
                          <tr class="text-left text-orange-900 font-bold border-b border-orange-300">
                            <th class="pb-2 pr-4">Строка</th>
                            <th class="pb-2 pr-4">Тип</th>
                            <th class="pb-2">Номер</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(item, index) in resultPrices.stats.rings_not_found_list" 
                              :key="index"
                              class="border-b border-orange-200 text-orange-700">
                            <td class="py-2 pr-4">{{ item.row }}</td>
                            <td class="py-2 pr-4">{{ item.typeCode }}</td>
                            <td class="py-2">{{ item.partNumber }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div v-if="resultPrices.stats.errors && resultPrices.stats.errors.length > 0" class="mt-6 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                    <h5 class="font-bold text-sm text-yellow-800 mb-2">Предупреждения:</h5>
                    <ul class="text-sm text-yellow-700 space-y-1 max-h-40 overflow-y-auto">
                      <li v-for="(error, index) in resultPrices.stats.errors" :key="index">{{ error }}</li>
                    </ul>
                  </div>
                </div>

                <button
                  @click="resetUploadPrices"
                  class="btn-primary px-8 py-4 text-base font-bold"
                >
                  Загрузить еще один файл
                </button>
              </div>

              <div v-else class="text-center">
                <svg class="w-20 h-20 mx-auto mb-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                
                <h3 class="heading-md mb-4 text-red-600">Ошибка импорта</h3>
                <p class="text-body mb-8">{{ resultPrices.message }}</p>
                
                <button
                  @click="resetUploadPrices"
                  class="btn-primary px-8 py-4 text-base font-bold"
                >
                  Попробовать снова
                </button>
              </div>
            </template>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import apiClient from '@/config/axios'

// Активная вкладка
const activeTab = ref('rings')

// Импорт товаров
const fileInput = ref(null)
const selectedFile = ref(null)
const isDragging = ref(false)
const uploading = ref(false)
const result = ref(null)

// Импорт цен и количества
const fileInputPrices = ref(null)
const selectedFilePrices = ref(null)
const isDraggingPrices = ref(false)
const uploadingPrices = ref(false)
const resultPrices = ref(null)

// Обработчики для импорта товаров
const handleFileDrop = (e) => {
  isDragging.value = false
  const files = e.dataTransfer.files
  
  if (files.length > 0) {
    const file = files[0]
    if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
      selectedFile.value = file
    } else {
      alert('Пожалуйста, загрузите файл в формате .xlsx или .xls')
    }
  }
}

const handleFileSelect = (e) => {
  const files = e.target.files
  if (files.length > 0) {
    selectedFile.value = files[0]
  }
}

const uploadFile = async () => {
  if (!selectedFile.value) return

  uploading.value = true
  result.value = null

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    const response = await apiClient.post('/api/import/rings', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      timeout: 600000 // 10 минут
    })

    result.value = response.data
  } catch (error) {
    result.value = {
      success: false,
      message: error.response?.data?.message || 'Произошла ошибка при загрузке файла'
    }
  } finally {
    uploading.value = false
  }
}

const resetUpload = () => {
  selectedFile.value = null
  result.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Обработчики для импорта цен и количества
const handleFileDropPrices = (e) => {
  isDraggingPrices.value = false
  const files = e.dataTransfer.files
  
  if (files.length > 0) {
    const file = files[0]
    if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
      selectedFilePrices.value = file
    } else {
      alert('Пожалуйста, загрузите файл в формате .xlsx или .xls')
    }
  }
}

const handleFileSelectPrices = (e) => {
  const files = e.target.files
  if (files.length > 0) {
    selectedFilePrices.value = files[0]
  }
}

const uploadFilePrices = async () => {
  if (!selectedFilePrices.value) return

  uploadingPrices.value = true
  resultPrices.value = null

  try {
    const formData = new FormData()
    formData.append('file', selectedFilePrices.value)

    const response = await apiClient.post('/api/import/prices-and-stock', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      timeout: 600000 // 10 минут
    })

    resultPrices.value = response.data
  } catch (error) {
    resultPrices.value = {
      success: false,
      message: error.response?.data?.message || 'Произошла ошибка при загрузке файла'
    }
  } finally {
    uploadingPrices.value = false
  }
}

const resetUploadPrices = () => {
  selectedFilePrices.value = null
  resultPrices.value = null
  if (fileInputPrices.value) {
    fileInputPrices.value.value = ''
  }
}
</script>
