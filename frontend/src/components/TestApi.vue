<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const message = ref('Chargement...')

onMounted(() => {
  api.get('hello')
    .then(res => {
      message.value = res.data.message
    })
    .catch(() => {
      message.value = 'Erreur de connexion'
    })
})
</script>

<template>
  <div style="padding: 20px; background: #f3f3f3;">
    <h2>Test API Laravel ↔ Vue.js</h2>
    <p>{{ message }}</p>
  </div>
</template>

<style>
h2 {
  color: rgb(52, 73, 94);
}
</style>
    