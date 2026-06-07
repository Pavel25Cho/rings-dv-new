import { defineStore } from 'pinia'
import apiClient from '@/config/axios'
import { useCartStore } from './cart'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
    initialized: false
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    isAdmin: (state) => state.user?.role === 'ADMIN'
  },

  actions: {
    setToken(token) {
      this.token = token
      if (token) {
        localStorage.setItem('token', token)
      } else {
        localStorage.removeItem('token')
      }
    },

    setUser(user) {
      this.user = user
    },

    async fetchUser() {
      if (!this.token) {
        this.initialized = true
        return
      }

      this.loading = true
      try {
        const response = await apiClient.get('/api/auth/me')
        this.user = response.data.user
        this.initialized = true
      } catch (error) {
        console.error('Failed to fetch user:', error)
        this.user = null
        this.token = null
        localStorage.removeItem('token')
        this.initialized = true
      } finally {
        this.loading = false
      }
    },

    async login(email, password) {
      this.loading = true
      try {
        const response = await apiClient.post('/api/auth/login', {
          email,
          password
        })
        
        this.setToken(response.data.token)
        this.setUser(response.data.user)
        this.initialized = true
        
        // Загружаем корзину после успешного входа
        const cartStore = useCartStore()
        cartStore.fetchCart()
        
        return response.data
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(email, password) {
      this.loading = true
      try {
        const response = await apiClient.post('/api/auth/register', {
          email,
          password
        })
        
        return response.data
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        await apiClient.post('/api/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        this.initialized = false
        localStorage.removeItem('token')
        this.loading = false
        
        // Очищаем корзину при выходе
        const cartStore = useCartStore()
        cartStore.clearLocalCart()
      }
    },

    async checkAuth() {
      if (this.token && !this.initialized) {
        await this.fetchUser()
      } else {
        this.initialized = true
      }
    }
  }
})
