<template>
  <div class="px-4 md:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <div class="glass-card rounded-3xl p-10 mb-8">
        <div class="flex items-center justify-between mb-6">
          <h1 class="heading-xl">Импорт товаров из Excel</h1>
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

        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-xl mb-8">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-base font-bold text-blue-800 mb-2">Инструкция по импорту</p>
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
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import apiClient from '@/config/axios'

const fileInput = ref(null)
const selectedFile = ref(null)
const isDragging = ref(false)
const uploading = ref(false)
const result = ref(null)

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
</script>
