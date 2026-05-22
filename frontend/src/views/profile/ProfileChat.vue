<template>
  <div>
    <div class="text-center py-12 text-gray-600">
      <p class="text-lg mb-4">Чат с администратором</p>
      <p class="text-sm">Функционал чата будет реализован позже</p>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>
