<template>
  <div id="app" class="flex flex-col min-h-screen">
    <Header />
    <main class="flex-1">
      <router-view />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'

const authStore = useAuthStore()

onMounted(() => {
  authStore.checkAuth()
})
</script>
