<template>
  <transition name="dropdown">
    <div 
      v-if="visible"
      class="absolute right-0 mt-3 w-96 glass-card-strongest rounded-2xl overflow-hidden max-h-[600px] flex flex-col"
    >
      <div class="px-5 py-4 border-b border-gray-200/50">
        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Корзина</p>
        <p class="text-base font-bold text-gray-900 mt-1">{{ cartCount }} {{ getItemsText(cartCount) }}</p>
      </div>

      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-purple-600"></div>
        <p class="mt-3 text-gray-600 text-sm">Загрузка...</p>
      </div>

      <div v-else-if="cartItems.length === 0" class="p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <p class="mt-4 text-gray-600 font-semibold">Корзина пуста</p>
        <p class="mt-1 text-sm text-gray-500">Добавьте товары из каталога</p>
      </div>

      <div v-else class="flex-1 overflow-y-auto p-3 space-y-3">
        <div
          v-for="item in cartItems"
          :key="item.ringId"
          class="bg-white/60 rounded-xl p-3 hover:bg-white/80 transition-colors"
        >
          <div class="flex items-start gap-3">
            <img
              v-if="item.ring?.photoUrl"
              :src="item.ring.photoUrl"
              :alt="item.ring.partNumber"
              class="w-16 h-16 object-contain rounded-lg"
            />
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-gray-900 text-sm truncate">{{ item.ring?.partNumber }}</p>
              <p class="text-xs text-gray-600 truncate mt-0.5">{{ item.ring?.group?.nameRu }}</p>
              <p class="text-purple-600 font-bold text-sm mt-1">{{ item.ring?.price }} ₽</p>
              
              <div class="flex items-center gap-2 mt-2">
                <button
                  @click="decrementQuantity(item)"
                  :disabled="item.quantity <= 1"
                  class="w-7 h-7 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                >
                  <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                  </svg>
                </button>
                
                <span class="text-sm font-semibold text-gray-900 w-8 text-center">{{ item.quantity }}</span>
                
                <button
                  @click="incrementQuantity(item)"
                  :disabled="item.quantity >= item.ring?.inStock"
                  class="w-7 h-7 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                >
                  <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                  </svg>
                </button>

                <button
                  @click="removeItem(item.ringId)"
                  class="ml-auto p-1.5 text-red-500 hover:bg-red-50 rounded transition-colors"
                  title="Удалить из корзины"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="cartItems.length > 0" class="border-t border-gray-200/50">
        <div class="px-5 py-3 bg-purple-50/50">
          <div class="flex justify-between items-center">
            <span class="text-gray-700 font-semibold">Итого:</span>
            <span class="text-purple-600 font-bold text-xl">{{ cartTotal }} ₽</span>
          </div>
        </div>
        
        <div class="p-3 space-y-2">
          <button
            @click="handleCheckout"
            class="w-full px-4 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold"
          >
            Перейти к заказу
          </button>
          
          <button
            @click="handleClearCart"
            class="w-full px-4 py-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors font-semibold text-sm"
          >
            Очистить корзину
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue'
import { useCartStore } from '@/stores/cart'

defineProps({
  visible: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close'])

const cartStore = useCartStore()

const cartItems = computed(() => cartStore.cartItems)
const cartCount = computed(() => cartStore.cartCount)
const cartTotal = computed(() => cartStore.cartTotal)
const loading = computed(() => cartStore.loading)

const getItemsText = (count) => {
  const lastDigit = count % 10
  const lastTwoDigits = count % 100

  if (lastTwoDigits >= 11 && lastTwoDigits <= 14) {
    return 'товаров'
  }

  if (lastDigit === 1) {
    return 'товар'
  }

  if (lastDigit >= 2 && lastDigit <= 4) {
    return 'товара'
  }

  return 'товаров'
}

const incrementQuantity = async (item) => {
  if (item.quantity >= item.ring?.inStock) return
  
  await cartStore.updateCartItem(item.ringId, item.quantity + 1)
}

const decrementQuantity = async (item) => {
  if (item.quantity <= 1) return
  
  await cartStore.updateCartItem(item.ringId, item.quantity - 1)
}

const removeItem = async (ringId) => {
  if (confirm('Удалить товар из корзины?')) {
    await cartStore.removeFromCart(ringId)
  }
}

const handleClearCart = async () => {
  if (confirm('Очистить всю корзину?')) {
    await cartStore.clearCart()
  }
}

const handleCheckout = () => {
  // TODO: Реализовать переход к оформлению заказа
  console.log('Переход к оформлению заказа')
  emit('close')
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.dropdown-enter-to,
.dropdown-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}
</style>
