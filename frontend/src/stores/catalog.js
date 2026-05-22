import { defineStore } from 'pinia'
import axios from 'axios'

export const useCatalogStore = defineStore('catalog', {
  state: () => ({
    groups: [],
    filters: {
      typeCode: '',
      brand: '',
      material: '',
      onlyInStock: false
    }
  }),

  actions: {
    async fetchGroups() {
      try {
        const response = await axios.get('/api/catalog/groups', {
          params: this.filters
        })
        this.groups = response.data
      } catch (error) {
        console.error('Error fetching groups:', error)
      }
    },

    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    }
  }
})
