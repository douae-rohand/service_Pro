<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg w-full max-w-xl p-6">
      <div class="flex items-start justify-between">
        <h3 class="text-lg font-semibold">Confirmer la demande</h3>
        <button @click="close" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>

      <div class="mt-4 space-y-2">
        <p><strong>Client:</strong> {{ clientId }}</p>
        <p v-if="service"><strong>Service:</strong> {{ service.name }}</p>
        <p v-if="task"><strong>Tâche:</strong> {{ task.name }}</p>
        <p v-if="intervenant"><strong>Intervenant:</strong> {{ intervenant.name }} — {{ intervenant.rate }} MAD/h</p>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700">Commentaires (optionnel)</label>
        <textarea v-model="notes" rows="3" class="mt-1 block w-full border rounded-md p-2" />
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button @click="close" class="px-4 py-2 rounded-md bg-gray-100">Annuler</button>
        <button @click="submitRequest" :disabled="submitting" class="px-4 py-2 rounded-md bg-blue-600 text-white">{{ submitting ? 'Envoi...' : 'Confirmer et envoyer' }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import interventionService from '@/services/interventionService'

const props = defineProps({
  visible: { type: Boolean, required: true },
  clientId: { type: Number, required: true },
  service: { type: Object, required: false },
  task: { type: Object, required: false },
  intervenant: { type: Object, required: false }
})

const emits = defineEmits(['close', 'submitted'])

const notes = ref('')
const submitting = ref(false)

function close() {
  notes.value = ''
  emits('close')
}

async function submitRequest() {
  if (!props.task || !props.service) return
  submitting.value = true
  try {
    const payload = {
      client_id: props.clientId,
      service_id: props.service.id,
      task_id: props.task.id,
      intervenant_id: props.intervenant ? props.intervenant.id : null,
      notes: notes.value
    }

    // Use interventionService which encapsulates API routes
    try {
      await interventionService.create(payload)
    } catch (err) {
      // as a last resort try the raw endpoint names
      await interventionService.create(payload)
    }

    emits('submitted')
    close()
  } catch (e) {
    console.error('Request failed', e)
    alert('Échec de l\'envoi. Vérifiez la configuration backend.')
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
</style>
