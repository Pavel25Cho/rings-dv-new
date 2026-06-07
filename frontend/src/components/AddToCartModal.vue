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
            class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6"
          >
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-bold text-gray-900">Добавить в корзину</h3>
              <button
                @click="closeModal"
                class="text-gray-500 hover:text-gray-700 transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div v-if="ring" class="space-y-4">
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-start gap-4">
                  <img
                    v-if="ring.photos && ring.photos.length > 0"
                    :src="ring.photos[0]"
                    :alt="ring.partNumber"
                    class="w-20 h-20 object-contain rounded-lg"
                  />
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-lg">{{ ring.partNumber }}</p>
                    <p class="text-purple-600 font-bold text-xl mt-1">{{ ring.price }} ₽</p>
                    <p class="text-sm text-gray-600 mt-1">В наличии: {{ ring.inStock }} шт.</p>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Количество
                </label>
                <div class="flex items-center gap-3">
                  <button
                    @click="decrementQuantity"
                    :disabled="quantity <= 1"
                    class="w-10 h-10 rounded-lg bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                  >
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                  </button>
                  
                  <input
                    v-model.number="quantity"
                    type="number"
                    min="1"
                    :max="ring.inStock"
                    class="flex-1 text-center text-lg font-semibold border-2 border-gray-200 rounded-lg px-4 py-2 focus:outline-none focus:border-purple-500 transition-colors"
                  />
                  
                  <button
                    @click="incrementQuantity"
                    :disabled="quantity >= ring.inStock"
                    class="w-10 h-10 rounded-lg bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                  >
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="bg-purple-50 rounded-xl p-4">
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 font-semibold">Итого:</span>
                  <span class="text-purple-600 font-bold text-2xl">{{ totalPrice }} ₽</span>
                </div>
              </div>

              <div v-if="error" class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm">
                {{ error }}
              </div>

              <div class="flex gap-3">
                <button
                  @click="closeModal"
                  class="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-semibold"
                >
                  Отмена
                </button>
                <button
                  @click="handleAddToCart"
                  :disabled="loading"
                  class="flex-1 px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="loading">Добавление...</span>
                  <span v-else>Добавить в корзину</span>
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  visible: {
    type: Boolean,
    required: true
  },
  ring: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'added'])

const cartStore = useCartStore()

const quantity = ref(1)
const loading = ref(false)
const error = ref('')

const totalPrice = computed(() => {
  if (!props.ring) return 0
  return props.ring.price * quantity.value
})

const incrementQuantity = () => {
  if (props.ring && quantity.value < props.ring.inStock) {
    quantity.value++
  }
}

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const handleAddToCart = async () => {
  if (!props.ring) return
  
  error.value = ''
  loading.value = true
  
  try {
    const result = await cartStore.addToCart(props.ring.id, quantity.value)
    
    if (result.success) {
      emit('added')
      closeModal()
    } else {
      error.value = result.message || 'Не удалось добавить товар в корзину'
    }
  } catch (err) {
    error.value = 'Произошла ошибка при добавлении товара'
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  quantity.value = 1
  error.value = ''
  emit('close')
}

watch(() => props.visible, (newVal) => {
  if (newVal) {
    quantity.value = 1
    error.value = ''
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})
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

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>
