<template>
  <div class="container">
    <!-- Placeholder for History Tab -->
    <div class="card">
      <h1>Historique des Missions</h1>
      <p class="subtitle">Consultez l'historique complet de vos missions et gains</p>
      
      <div class="empty-state">
        <History :size="48" />
        <p>Fonctionnalité en cours de développement</p>
        <p class="text-sm">Cette section affichera l'historique de toutes vos missions complétées.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { History } from 'lucide-vue-next'
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
  color: var(--color-primary-green);
}
</style>
