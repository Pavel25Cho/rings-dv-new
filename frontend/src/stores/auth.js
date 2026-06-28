import { defineStore } from 'pinia'
import apiClient from '@/config/axios'
import { useCartStore } from './cart'
import { useChatStore } from './chat'

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
        
        // Загружаем корзину и уведомления после успешной авторизации
        const cartStore = useCartStore()
        const chatStore = useChatStore()
        cartStore.fetchCart()
        chatStore.fetchUnreadCount()
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
        
        // Загружаем корзину и уведомления после успешного входа
        const cartStore = useCartStore()
        const chatStore = useChatStore()
        cartStore.fetchCart()
        chatStore.fetchUnreadCount()
        
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
        
        // Очищаем корзину и чаты при выходе
        const cartStore = useCartStore()
        const chatStore = useChatStore()
        cartStore.clearLocalCart()
        chatStore.clearMessages()
        chatStore.unreadCount = 0
      }
    },

    async checkAuth() {
      if (this.token && !this.initialized) {
        await this.fetchUser()
      } else {
        this.initialized = true
      }
    },

    async updateProfile(profileData) {
      this.loading = true
      try {
        const response = await apiClient.put('/api/auth/profile', profileData)
        this.user = response.data.user
        return response.data
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    },

    async sendVerificationEmail() {
      try {
        const response = await apiClient.post('/api/auth/send-verification-email')
        return response.data
      } catch (error) {
        throw error
      }
    }
  }
})
