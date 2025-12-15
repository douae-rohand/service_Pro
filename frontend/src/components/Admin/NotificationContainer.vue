<template>
  <div class="notification-container">
    <!-- Notifications normales -->
    <div
      v-for="notification in normalNotifications"
      :key="notification.id"
      :style="getNotificationStyle(notification)"
    >
      <NotificationToast
        :message="notification.message"
        :type="notification.type"
        :duration="notification.duration"
        :position="notification.position"
        @close="removeNotification(notification.id)"
      />
    </div>

    <!-- Modal de confirmation -->
    <Transition name="modal">
      <div
        v-if="confirmNotification"
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50 p-4"
        @click.self="handleConfirmCancel"
      >
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
          <div class="flex items-start gap-4 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
              <AlertTriangle :size="24" class="text-yellow-600" />
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">
                {{ confirmNotification.title || 'Confirmation' }}
              </h3>
              <p class="text-sm text-gray-600 whitespace-pre-line">
                {{ confirmNotification.message }}
              </p>
            </div>
          </div>
          <div class="flex gap-3 justify-end">
            <button
              @click="handleConfirmCancel"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors border border-gray-300 text-gray-700 hover:bg-gray-50"
            >
              Annuler
            </button>
            <button
              @click="handleConfirmOk"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors text-white hover:opacity-90"
              style="background-color: #5B7C99"
            >
              Confirmer
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useNotifications } from '@/composables/useNotifications'
import NotificationToast from './NotificationToast.vue'
import { AlertTriangle } from 'lucide-vue-next'

const { notifications, remove } = useNotifications()

const normalNotifications = computed(() => {
  return notifications.value.filter(n => n.type !== 'confirm')
})

const confirmNotification = computed(() => {
  return notifications.value.find(n => n.type === 'confirm' && n.show)
})

const removeNotification = (id) => {
  remove(id)
}

const getNotificationStyle = (notification) => {
  // Calculer la position verticale en fonction de l'index pour empiler les notifications
  const index = normalNotifications.value.findIndex(n => n.id === notification.id)
  const offset = index * 80 // 80px entre chaque notification
  
  const baseStyle = {}
  if (notification.position.includes('top')) {
    baseStyle.top = `${1 + (offset / 16)}rem`
  } else if (notification.position.includes('bottom')) {
    baseStyle.bottom = `${1 + (offset / 16)}rem`
  }
  if (notification.position.includes('center')) {
    baseStyle.left = '50%'
    baseStyle.transform = 'translateX(-50%)'
  } else if (notification.position.includes('right')) {
    baseStyle.right = '1rem'
  } else if (notification.position.includes('left')) {
    baseStyle.left = '1rem'
  }
  return baseStyle
}

const handleConfirmOk = () => {
  if (confirmNotification.value && confirmNotification.value.resolve) {
    confirmNotification.value.resolve(true)
    remove(confirmNotification.value.id)
  }
}

const handleConfirmCancel = () => {
  if (confirmNotification.value && confirmNotification.value.resolve) {
    confirmNotification.value.resolve(false)
    remove(confirmNotification.value.id)
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
