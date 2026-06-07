import { defineStore } from 'pinia'
import apiClient from '@/config/axios'

export const useCartStore = defineStore('cart', {
  state: () => ({
    cart: [],
    loading: false
  }),

  getters: {
    cartItems: (state) => state.cart,
    cartCount: (state) => state.cart.reduce((total, item) => total + item.quantity, 0),
    cartTotal: (state) => {
      return state.cart.reduce((total, item) => {
        const price = item.ring?.price || 0
        return total + (price * item.quantity)
      }, 0)
    }
  },

  actions: {
    async fetchCart() {
      this.loading = true
      try {
        const response = await apiClient.get('/api/cart')
        this.cart = response.data.cart || []
      } catch (error) {
        console.error('Ошибка загрузки корзины:', error)
        this.cart = []
      } finally {
        this.loading = false
      }
    },

    async addToCart(ringId, quantity) {
      this.loading = true
      try {
        const response = await apiClient.post('/api/cart/add', {
          ringId,
          quantity
        })
        
        if (response.data.success) {
          await this.fetchCart()
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        console.error('Ошибка добавления в корзину:', error)
        const message = error.response?.data?.message || 'Не удалось добавить товар в корзину'
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async updateCartItem(ringId, quantity) {
      this.loading = true
      try {
        const response = await apiClient.post('/api/cart/update', {
          ringId,
          quantity
        })
        
        if (response.data.success) {
          await this.fetchCart()
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        console.error('Ошибка обновления корзины:', error)
        const message = error.response?.data?.message || 'Не удалось обновить количество'
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async removeFromCart(ringId) {
      this.loading = true
      try {
        const response = await apiClient.post('/api/cart/remove', {
          ringId
        })
        
        if (response.data.success) {
          await this.fetchCart()
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        console.error('Ошибка удаления из корзины:', error)
        const message = error.response?.data?.message || 'Не удалось удалить товар'
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async clearCart() {
      this.loading = true
      try {
        const response = await apiClient.post('/api/cart/clear')
        
        if (response.data.success) {
          this.cart = []
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        console.error('Ошибка очистки корзины:', error)
        const message = error.response?.data?.message || 'Не удалось очистить корзину'
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    clearLocalCart() {
      this.cart = []
    }
  }
})
