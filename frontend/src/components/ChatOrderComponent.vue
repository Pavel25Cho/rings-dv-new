<template>
  <div class="bg-purple-50/50 rounded-xl p-4 border-2 border-purple-200">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <span class="font-bold text-gray-900">Заказ #{{ localOrder.id }}</span>
      </div>
      
      <div class="flex items-center gap-2">
        <span 
          class="px-3 py-1 rounded-full text-xs font-semibold"
          :class="statusClasses"
        >
          {{ statusText }}
        </span>
      </div>
    </div>

    <div class="space-y-3">
      <div
        v-for="item in localOrder.items"
        :key="item.id"
        class="bg-white rounded-lg p-3"
      >
        <div class="flex items-start gap-3">
          <img
            v-if="item.photoUrl"
            :src="item.photoUrl"
            :alt="item.partNumber"
            class="w-16 h-16 object-contain rounded-lg"
          />
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 text-sm">{{ item.brand }} {{ item.partNumber }}</p>
            <p class="text-purple-600 font-bold text-sm mt-1">{{ item.price }} ₽</p>
            
            <div class="flex items-center gap-3 mt-2">
              <div v-if="canEdit" class="flex items-center gap-2">
                <button
                  @click="decrementQuantity(item)"
                  :disabled="item.quantity <= 1 || updating"
                  class="w-7 h-7 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                >
                  <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                  </svg>
                </button>
                
                <span class="text-sm font-semibold text-gray-900 w-8 text-center">{{ item.quantity }}</span>
                
                <button
                  @click="incrementQuantity(item)"
                  :disabled="updating || (item.inStock !== null && item.quantity >= item.inStock)"
                  class="w-7 h-7 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                >
                  <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              
              <div v-else class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Количество:</span>
                <span class="text-sm font-semibold text-gray-900">{{ item.quantity }} шт.</span>
              </div>

              <div class="ml-auto text-right">
                <p class="text-xs text-gray-500">Сумма:</p>
                <p class="text-sm font-bold text-gray-900">{{ item.totalPrice }} ₽</p>
              </div>
            </div>

            <div v-if="item.inStock !== null" class="mt-1">
              <p 
                class="text-xs"
                :class="item.inStock >= item.quantity ? 'text-green-600' : 'text-red-600'"
              >
                В наличии: {{ item.inStock }} шт.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 pt-4 border-t-2 border-purple-200">
      <div class="flex justify-between items-center mb-3">
        <span class="text-gray-700 font-semibold">Итого:</span>
        <span class="text-purple-600 font-bold text-xl">{{ localOrder.totalPrice }} ₽</span>
      </div>

      <div v-if="error" class="mb-3 bg-red-50 text-red-600 px-3 py-2 rounded-lg text-sm">
        {{ error }}
      </div>

      <div v-if="stockCheckResult" class="mb-3">
        <div 
          v-if="stockCheckResult.hasIssues"
          class="bg-orange-50 text-orange-800 px-3 py-2 rounded-lg text-sm"
        >
          <p class="font-semibold mb-1">Проблемы с наличием:</p>
          <ul class="space-y-1">
            <li 
              v-for="item in stockCheckResult.items.filter(i => !i.isAvailable)" 
              :key="item.partNumber"
              class="text-xs"
            >
              {{ item.partNumber }}: нужно {{ item.needed }}, доступно {{ item.available }}
            </li>
          </ul>
        </div>
        <div v-else class="bg-green-50 text-green-800 px-3 py-2 rounded-lg text-sm">
          ✓ Все товары в наличии
        </div>
      </div>

      <div v-if="isAdmin && localOrder.status === 'pending'" class="flex gap-2">
        <button
          @click="handleCheckStock"
          :disabled="checking"
          class="flex-1 px-4 py-2 border-2 border-purple-300 text-purple-700 rounded-lg hover:bg-purple-50 transition-colors font-semibold text-sm disabled:opacity-50"
        >
          <span v-if="checking">Проверка...</span>
          <span v-else>Проверить количество</span>
        </button>
        <button
          @click="handleConfirm"
          :disabled="confirming"
          class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm disabled:opacity-50"
        >
          <span v-if="confirming">Подтверждение...</span>
          <span v-else>Подтвердить заказ</span>
        </button>
      </div>

      <div v-if="isAdmin && localOrder.status === 'confirmed'" class="flex gap-2">
        <button
          @click="handleCancel"
          :disabled="cancelling"
          class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm disabled:opacity-50"
        >
          <span v-if="cancelling">Отмена...</span>
          <span v-else>Отменить заказ</span>
        </button>
      </div>

      <div v-if="localOrder.confirmedAt" class="mt-3 text-xs text-gray-500">
        Подтвержден: {{ formatDate(localOrder.confirmedAt) }}
      </div>
      <div v-if="localOrder.cancelledAt" class="mt-3 text-xs text-gray-500">
        Отменен: {{ formatDate(localOrder.cancelledAt) }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  order: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['updated'])

const chatStore = useChatStore()
const authStore = useAuthStore()

const updating = ref(false)
const confirming = ref(false)
const cancelling = ref(false)
const checking = ref(false)
const error = ref('')
const stockCheckResult = ref(null)
const localOrder = ref({ ...props.order })

// Следим за изменениями в props.order и обновляем localOrder
watch(() => props.order, (newOrder) => {
  localOrder.value = { ...newOrder }
}, { deep: true, immediate: true })

const isAdmin = computed(() => authStore.isAdmin)
const canEdit = computed(() => {
  return localOrder.value.status === 'pending' && (isAdmin.value || !localOrder.value.confirmedAt)
})

const statusClasses = computed(() => {
  switch (localOrder.value.status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'confirmed':
      return 'bg-green-100 text-green-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
})

const statusText = computed(() => {
  switch (localOrder.value.status) {
    case 'pending':
      return 'Ожидает подтверждения'
    case 'confirmed':
      return 'Подтвержден'
    case 'cancelled':
      return 'Отменен'
    default:
      return localOrder.value.status
  }
})

const incrementQuantity = async (item) => {
  if (updating.value) return
  
  error.value = ''
  updating.value = true
  
  try {
    const result = await chatStore.updateOrderItem(item.id, item.quantity + 1)
    
    if (result.success) {
      emit('updated', result.order)
      stockCheckResult.value = null
    } else {
      error.value = result.message
    }
  } catch (err) {
    error.value = 'Произошла ошибка при обновлении'
  } finally {
    updating.value = false
  }
}

const decrementQuantity = async (item) => {
  if (updating.value || item.quantity <= 1) return
  
  error.value = ''
  updating.value = true
  
  try {
    const result = await chatStore.updateOrderItem(item.id, item.quantity - 1)
    
    if (result.success) {
      localOrder.value = { ...result.order }
      emit('updated', result.order)
      stockCheckResult.value = null
    } else {
      error.value = result.message
    }
  } catch (err) {
    error.value = 'Произошла ошибка при обновлении'
  } finally {
    updating.value = false
  }
}

const handleConfirm = async () => {
  if (!confirm('Подтвердить заказ? Количество товаров будет вычтено из каталога.')) {
    return
  }
  
  error.value = ''
  confirming.value = true
  
  try {
    const result = await chatStore.confirmOrder(localOrder.value.id)
    
    if (result.success) {
      localOrder.value = { ...result.order }
      emit('updated', result.order)
      stockCheckResult.value = null
    } else {
      error.value = result.message
    }
  } catch (err) {
    error.value = 'Произошла ошибка при подтверждении'
  } finally {
    confirming.value = false
  }
}

const handleCancel = async () => {
  if (!confirm('Отменить заказ? Товары будут возвращены в каталог.')) {
    return
  }
  
  error.value = ''
  cancelling.value = true
  
  try {
    const result = await chatStore.cancelOrder(localOrder.value.id)
    
    if (result.success) {
      localOrder.value = { ...result.order }
      emit('updated', result.order)
      stockCheckResult.value = null
    } else {
      error.value = result.message
    }
  } catch (err) {
    error.value = 'Произошла ошибка при отмене'
  } finally {
    cancelling.value = false
  }
}

const handleCheckStock = async () => {
  error.value = ''
  checking.value = true
  
  try {
    const result = await chatStore.checkOrderStock(localOrder.value.id)
    
    if (result.success) {
      stockCheckResult.value = result.data
    } else {
      error.value = result.message
    }
  } catch (err) {
    error.value = 'Произошла ошибка при проверке'
  } finally {
    checking.value = false
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
