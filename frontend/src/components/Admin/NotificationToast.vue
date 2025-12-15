<template>
  <Transition name="toast">
    <div
      v-if="show"
      class="notification-toast min-w-80 max-w-md"
      :class="{
        'bg-green-50 border-green-200': type === 'success',
        'bg-red-50 border-red-200': type === 'error',
        'bg-blue-50 border-blue-200': type === 'info',
        'bg-yellow-50 border-yellow-200': type === 'warning'
      }"
      :style="toastStyle"
    >
      <div class="flex items-start gap-3">
        <div
          class="flex-shrink-0 mt-0.5"
          :class="{
            'text-green-600': type === 'success',
            'text-red-600': type === 'error',
            'text-blue-600': type === 'info',
            'text-yellow-600': type === 'warning'
          }"
        >
          <CheckCircle v-if="type === 'success'" :size="20" />
          <XCircle v-if="type === 'error'" :size="20" />
          <Info v-if="type === 'info'" :size="20" />
          <AlertTriangle v-if="type === 'warning'" :size="20" />
        </div>
        <div class="flex-1 min-w-0">
          <p
            class="text-sm font-medium break-words"
            :class="{
              'text-green-800': type === 'success',
              'text-red-800': type === 'error',
              'text-blue-800': type === 'info',
              'text-yellow-800': type === 'warning'
            }"
          >
            {{ message }}
          </p>
        </div>
        <button
          @click="close"
          class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors p-1"
          aria-label="Fermer"
        >
          <X :size="18" />
        </button>
      </div>
      <!-- Progress bar pour la durée -->
      <div
        v-if="duration > 0"
        class="absolute bottom-0 left-0 right-0 h-1 bg-black bg-opacity-10 rounded-b-lg overflow-hidden"
      >
        <div
          class="h-full transition-all linear"
          :class="{
            'bg-green-500': type === 'success',
            'bg-red-500': type === 'error',
            'bg-blue-500': type === 'info',
            'bg-yellow-500': type === 'warning'
          }"
          :style="{ width: progressWidth + '%' }"
        />
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { CheckCircle, XCircle, Info, AlertTriangle, X } from 'lucide-vue-next'

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'info', 'warning'].includes(value)
  },
  duration: {
    type: Number,
    default: 4000
  },
  position: {
    type: String,
    default: 'top-right',
    validator: (value) => ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top-center', 'bottom-center'].includes(value)
  }
})

const emit = defineEmits(['close'])

const show = ref(false)
const progressWidth = ref(100)
let timeoutId = null
let progressInterval = null

const toastStyle = computed(() => {
  const positions = {
    'top-right': { top: '1rem', right: '1rem' },
    'top-left': { top: '1rem', left: '1rem' },
    'bottom-right': { bottom: '1rem', right: '1rem' },
    'bottom-left': { bottom: '1rem', left: '1rem' },
    'top-center': { top: '1rem', left: '50%', transform: 'translateX(-50%)' },
    'bottom-center': { bottom: '1rem', left: '50%', transform: 'translateX(-50%)' }
  }
  
  const baseStyle = {
    borderWidth: '2px',
    borderRadius: '12px',
    padding: '16px',
    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
    position: 'fixed',
    zIndex: 9999
  }
  
  return { ...baseStyle, ...positions[props.position] }
})

const startProgress = () => {
  if (props.duration <= 0) return
  
  const startTime = Date.now()
  const updateProgress = () => {
    const elapsed = Date.now() - startTime
    const remaining = Math.max(0, props.duration - elapsed)
    progressWidth.value = (remaining / props.duration) * 100
    
    if (remaining <= 0) {
      clearInterval(progressInterval)
      close()
    }
  }
  
  progressInterval = setInterval(updateProgress, 50)
}

onMounted(() => {
  show.value = true
  if (props.duration > 0) {
    startProgress()
    timeoutId = setTimeout(() => {
      close()
    }, props.duration)
  }
})

onUnmounted(() => {
  if (timeoutId) {
    clearTimeout(timeoutId)
  }
  if (progressInterval) {
    clearInterval(progressInterval)
  }
})

const close = () => {
  show.value = false
  if (timeoutId) {
    clearTimeout(timeoutId)
  }
  if (progressInterval) {
    clearInterval(progressInterval)
  }
  setTimeout(() => {
    emit('close')
  }, 300) // Wait for transition
}

watch(() => props.message, () => {
  if (timeoutId) {
    clearTimeout(timeoutId)
  }
  if (progressInterval) {
    clearInterval(progressInterval)
  }
  progressWidth.value = 100
  if (props.duration > 0) {
    startProgress()
    timeoutId = setTimeout(() => {
      close()
    }, props.duration)
  }
})
</script>

<style scoped>
.notification-toast {
  cursor: pointer;
}

.notification-toast:hover {
  transform: scale(1.02);
  transition: transform 0.2s ease;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}
</style>
