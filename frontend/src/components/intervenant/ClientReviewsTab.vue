<template>
  <div class="container">
    <!-- Placeholder for Client Reviews Tab -->
    <div class="card">
      <h1>Évaluer les Clients</h1>
      <p class="subtitle">Évaluez vos clients après avoir complété une mission</p>
      
      <div class="empty-state">
        <MessageCircle :size="48" />
        <p>Fonctionnalité en cours de développement</p>
        <p class="text-sm">Cette section vous permettra d'évaluer vos clients après chaque mission.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { MessageCircle } from 'lucide-vue-next'
</script>

<style scoped>
.container {
  max-width: 80rem;
}

.card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--spacing-6);
}

.card h1 {
  color: var(--color-dark);
  margin-bottom: var(--spacing-2);
}

.subtitle {
  color: var(--color-gray-600);
  font-size: 0.875rem;
  margin-bottom: var(--spacing-6);
}

.empty-state {
  text-align: center;
  padding: var(--spacing-12) var(--spacing-6);
  color: var(--color-gray-500);
}

.empty-state svg {
  margin: 0 auto var(--spacing-4);
  color: var(--color-yellow);
}
</style>
