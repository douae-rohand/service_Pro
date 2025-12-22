<template>
  <div v-if="show" class="modal-overlay" @click="close">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Nouvelle Réclamation</h2>
        <button @click="close" class="btn-close">
          <X :size="24" />
        </button>
      </div>

      <form @submit.prevent="submitReclamation" class="reclamation-form">
        <div class="form-group">
          <label>Intervenant concerné <span class="required">*</span></label>
          <select
            v-model="form.concernant_id"
            required
            :disabled="loadingIntervenants || preselectedIntervenantId"
          >
            <option value="">Sélectionner un intervenant...</option>
            <option
              v-for="intervenant in intervenants"
              :key="intervenant.id"
              :value="intervenant.id"
            >
              {{ intervenant.utilisateur?.prenom }} {{ intervenant.utilisateur?.nom }}
            </option>
          </select>
          <p v-if="preselectedIntervenantId" class="form-hint">
            Intervenant pré-sélectionné
          </p>
        </div>

        <div v-if="preselectedIntervention" class="form-group info-box">
          <label>Intervention liée</label>
          <div class="intervention-details">
            <p><strong>Service:</strong> {{ preselectedIntervention.service }}</p>
            <p><strong>Tâche:</strong> {{ preselectedIntervention.task }}</p>
            <p><strong>Date:</strong> {{ formatDate(preselectedIntervention.date) }}</p>
          </div>
        </div>

        <div class="form-group">
          <label>Raison <span class="required">*</span></label>
          <input
            v-model="form.raison"
            type="text"
            placeholder="Ex: Comportement inapproprié, Service non rendu..."
            required
            maxlength="255"
          />
        </div>

        <div class="form-group">
          <label>Message <span class="required">*</span></label>
          <textarea
            v-model="form.message"
            placeholder="Décrivez votre réclamation en détail..."
            required
            rows="6"
          ></textarea>
        </div>

        <div class="form-group">
          <label>Priorité</label>
          <select v-model="form.priorite">
            <option value="moyenne">Moyenne</option>
            <option value="haute">Haute</option>
            <option value="basse">Basse</option>
          </select>
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <div class="modal-actions">
          <button type="submit" :disabled="submitting" class="btn-submit">
            <span v-if="submitting">Envoi en cours...</span>
            <span v-else>Envoyer la Réclamation</span>
          </button>
          <button type="button" @click="close" class="btn-cancel">
            Annuler
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { X } from 'lucide-vue-next'
import clientReclamationService from '@/services/clientReclamationService'
import intervenantService from '@/services/intervenantService'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  preselectedIntervenantId: {
    type: Number,
    default: null
  },
  preselectedIntervention: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const submitting = ref(false)
const error = ref(null)
const loadingIntervenants = ref(false)
const intervenants = ref([])

const form = ref({
  concernant_id: '',
  intervention_id: null,
  raison: '',
  message: '',
  priorite: 'moyenne'
})

// Watch for preselected intervenant or intervention
watch(() => props.preselectedIntervenantId, (newId) => {
  if (newId) {
    form.value.concernant_id = newId.toString()
  }
}, { immediate: true })

watch(() => props.preselectedIntervention, (newInt) => {
  if (newInt) {
    form.value.intervention_id = newInt.id
    if (newInt.intervenant?.id) {
      form.value.concernant_id = newInt.intervenant.id.toString()
    }
  }
}, { immediate: true })

onMounted(async () => {
  await loadIntervenants()
})

async function loadIntervenants() {
  loadingIntervenants.value = true
  try {
    const response = await intervenantService.getAll({ active: 'true' })
    intervenants.value = response.data || []
  } catch (err) {
    console.error('Error loading intervenants:', err)
  } finally {
    loadingIntervenants.value = false
  }
}

async function submitReclamation() {
  if (!form.value.concernant_id || !form.value.raison || !form.value.message) {
    error.value = 'Veuillez remplir tous les champs obligatoires'
    return
  }

  submitting.value = true
  error.value = null

  try {
    await clientReclamationService.create({
      concernant_id: parseInt(form.value.concernant_id),
      concernant_type: 'Intervenant',
      intervention_id: form.value.intervention_id,
      raison: form.value.raison,
      message: form.value.message,
      priorite: form.value.priorite
    })

    // Reset form
    form.value = {
      concernant_id: props.preselectedIntervenantId ? props.preselectedIntervenantId.toString() : '',
      raison: '',
      message: '',
      priorite: 'moyenne'
    }

    emit('success')
    close()
    
    alert('Réclamation créée avec succès!')
  } catch (err) {
    console.error('Error creating reclamation:', err)
    console.error('Error response:', err.response?.data)
    
    // Show detailed error message
    if (err.response?.data?.errors) {
      // Validation errors
      const errorMessages = Object.values(err.response.data.errors).flat()
      error.value = errorMessages.join(', ')
    } else if (err.response?.data?.error) {
      // Debug error message
      error.value = err.response.data.error
    } else if (err.response?.data?.message) {
      // General error message
      error.value = err.response.data.message
    } else {
      error.value = 'Erreur lors de la création de la réclamation. Veuillez réessayer.'
    }
  } finally {
    submitting.value = false
  }
}

function close() {
  emit('close')
  // Reset form but keep props-based pre-selections
  resetForm()
  error.value = null
}

function resetForm() {
  form.value = {
    concernant_id: props.preselectedIntervenantId ? props.preselectedIntervenantId.toString() : '',
    intervention_id: props.preselectedIntervention ? props.preselectedIntervention.id : null,
    raison: '',
    message: '',
    priorite: 'moyenne'
  }
  
  if (props.preselectedIntervention?.intervenant?.id) {
    form.value.concernant_id = props.preselectedIntervention.intervenant.id.toString()
  }
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #eee;
}

.modal-header h2 {
  font-size: 24px;
  color: #2F4F4F;
}

.btn-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  padding: 4px;
}

.btn-close:hover {
  color: #2F4F4F;
}

.reclamation-form {
  padding: 24px;
}

.info-box {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.intervention-details p {
  margin: 4px 0;
  font-size: 14px;
  color: #475569;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #2F4F4F;
}

.required {
  color: #e74c3c;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}

.form-group textarea {
  resize: vertical;
}

.form-hint {
  font-size: 12px;
  color: #666;
  margin-top: 4px;
}

.error-message {
  padding: 12px;
  background: #FFEBEE;
  color: #C62828;
  border-radius: 6px;
  margin-bottom: 16px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 24px;
  border-top: 1px solid #eee;
}

.btn-submit {
  padding: 12px 24px;
  background: #92B08B;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.3s;
}

.btn-submit:hover:not(:disabled) {
  background: #7a9a73;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-cancel {
  padding: 12px 24px;
  background: #f5f5f5;
  color: #666;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.3s;
}

.btn-cancel:hover {
  background: #e0e0e0;
}
</style>

