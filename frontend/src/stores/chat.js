import { defineStore } from 'pinia'
import apiClient from '@/config/axios'

export const useChatStore = defineStore('chat', {
  state: () => ({
    myChat: null,
    chats: [],
    messages: [],
    unreadCount: 0,
    loading: false,
    sendingMessage: false,
    pagination: {
      total: 0,
      limit: 10,
      offset: 0,
      hasMore: false
    },
    loadingOlderMessages: false
  }),

  getters: {
    hasUnread: (state) => state.unreadCount > 0
  },

  actions: {
    async fetchMyChat() {
      this.loading = true
      try {
        const response = await apiClient.get('/api/chat/my')
        if (response.data.success) {
          this.myChat = response.data.chat
        }
      } catch (error) {
        console.error('Ошибка загрузки чата:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchChatList() {
      this.loading = true
      try {
        const response = await apiClient.get('/api/chat/list')
        if (response.data.success) {
          this.chats = response.data.chats
        }
      } catch (error) {
        console.error('Ошибка загрузки списка чатов:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchMessages(chatId, options = {}) {
      const { limit = 10, offset = 0, append = false } = options
      
      this.loading = !append
      this.loadingOlderMessages = append
      
      try {
        const response = await apiClient.get(`/api/chat/messages/${chatId}`, {
          params: { limit, offset }
        })
        
        if (response.data.success) {
          if (append) {
            // Добавляем старые сообщения в начало массива
            this.messages = [...response.data.messages, ...this.messages]
          } else {
            // Заменяем все сообщения
            this.messages = response.data.messages
          }
          
          // Обновляем информацию о пагинации
          this.pagination = response.data.pagination
        }
      } catch (error) {
        console.error('Ошибка загрузки сообщений:', error)
      } finally {
        this.loading = false
        this.loadingOlderMessages = false
      }
    },

    async loadOlderMessages(chatId) {
      if (!this.pagination.hasMore || this.loadingOlderMessages) {
        return
      }
      
      const newOffset = this.pagination.offset + this.pagination.limit
      await this.fetchMessages(chatId, {
        limit: this.pagination.limit,
        offset: newOffset,
        append: true
      })
    },

    resetPagination() {
      this.pagination = {
        total: 0,
        limit: 10,
        offset: 0,
        hasMore: false
      }
    },

    async sendMessage(chatId, text) {
      this.sendingMessage = true
      try {
        const response = await apiClient.post('/api/chat/send-message', {
          chatId,
          text
        })
        
        if (response.data.success) {
          this.messages.push(response.data.message)
          return { success: true }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        console.error('Ошибка отправки сообщения:', error)
        const message = error.response?.data?.message || 'Не удалось отправить сообщение'
        return { success: false, message }
      } finally {
        this.sendingMessage = false
      }
    },

    async createOrder(items) {
      this.loading = true
      try {
        const response = await apiClient.post('/api/chat/create-order', {
          items
        })
        
        if (response.data.success) {
          this.myChat = response.data.chat
          this.messages = []
          await this.fetchMessages(response.data.chat.id)
          return { success: true, chat: response.data.chat }
        }
        
        return { success: false, message: response.data.message }
      } catch (error) {
        console.error('Ошибка создания заказа:', error)
        const message = error.response?.data?.message || 'Не удалось создать заказ'
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async markAsRead(chatId) {
      try {
        await apiClient.post(`/api/chat/mark-read/${chatId}`)
        await this.fetchUnreadCount()
      } catch (error) {
        console.error('Ошибка пометки сообщений как прочитанных:', error)
      }
    },

    async fetchUnreadCount() {
      try {
        const response = await apiClient.get('/api/chat/unread-count')
        if (response.data.success) {
          this.unreadCount = response.data.count
        }
      } catch (error) {
        console.error('Ошибка загрузки количества непрочитанных:', error)
      }
    },

    async confirmOrder(orderId) {
      try {
        const response = await apiClient.post(`/api/admin/orders/confirm/${orderId}`)
        
        if (response.data.success) {
          return { success: true, order: response.data.order }
        }
        
        return { success: false, message: response.data.error }
      } catch (error) {
        console.error('Ошибка подтверждения заказа:', error)
        const message = error.response?.data?.error || 'Не удалось подтвердить заказ'
        return { success: false, message }
      }
    },

    async cancelOrder(orderId) {
      try {
        const response = await apiClient.post(`/api/admin/orders/cancel/${orderId}`)
        
        if (response.data.success) {
          return { success: true, order: response.data.order }
        }
        
        return { success: false, message: response.data.error }
      } catch (error) {
        console.error('Ошибка отмены заказа:', error)
        const message = error.response?.data?.error || 'Не удалось отменить заказ'
        return { success: false, message }
      }
    },

    async updateOrderItem(itemId, quantity) {
      try {
        // Используем endpoint из ChatController, который доступен всем пользователям
        const response = await apiClient.post(`/api/chat/update-order-item/${itemId}`, {
          quantity
        })
        
        if (response.data.success) {
          return { success: true, order: response.data.order }
        }
        
        return { success: false, message: response.data.message || response.data.error }
      } catch (error) {
        console.error('Ошибка обновления товара:', error)
        const message = error.response?.data?.message || error.response?.data?.error || 'Не удалось обновить количество'
        return { success: false, message }
      }
    },

    async checkOrderStock(orderId) {
      try {
        const response = await apiClient.get(`/api/admin/orders/check-stock/${orderId}`)
        
        if (response.data.success) {
          return { success: true, data: response.data }
        }
        
        return { success: false, message: response.data.error }
      } catch (error) {
        console.error('Ошибка проверки наличия:', error)
        const message = error.response?.data?.error || 'Не удалось проверить наличие'
        return { success: false, message }
      }
    },

    clearMessages() {
      this.messages = []
      this.resetPagination()
    },

    updateOrderInMessages(updatedOrder) {
      // Находим сообщение с этим заказом и обновляем его
      const messageIndex = this.messages.findIndex(
        msg => msg.order && msg.order.id === updatedOrder.id
      )
      
      if (messageIndex !== -1) {
        // Обновляем заказ в сообщении без перезагрузки всего массива
        this.messages[messageIndex].order = updatedOrder
      }
    }
  }
})
