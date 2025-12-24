<template>
  <Transition name="modal-fade">
    <div v-if="show" class="modal-overlay">
      <div class="modal-container">
        <div class="loader-container">
          <div class="spinner"></div>
          <p class="loading-message">{{ message }}</p>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    required: true
  },
  message: {
    type: String,
    default: 'Chargement en cours...'
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-container {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  max-width: 400px;
  width: 90%;
}

.loader-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #92B08B;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-message {
  font-size: 1rem;
  color: #374151;
  font-weight: 500;
  margin: 0;
  text-align: center;
}

/* Transition animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .modal-container,
.modal-fade-leave-active .modal-container {
  transition: transform 0.2s ease;
}

.modal-fade-enter-from .modal-container,
.modal-fade-leave-to .modal-container {
  transform: scale(0.9);
}
</style>
