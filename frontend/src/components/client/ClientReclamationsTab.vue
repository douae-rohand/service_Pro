<template>
  <div class="client-reclamations-tab">
    <div class="tab-header">
      <h2 class="tab-title">Mes Réclamations</h2>
      <button @click="showCreateModal = true" class="btn-create">
        <Plus :size="20" />
        Nouvelle Réclamation
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="filter-group">
        <label>Statut:</label>
        <select v-model="filters.statut" @change="loadReclamations">
          <option value="all">Tous</option>
          <option value="en_attente">En attente</option>
          <option value="en_cours">En cours</option>
          <option value="resolu">Résolu</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Priorité:</label>
        <select v-model="filters.priorite" @change="loadReclamations">
          <option value="all">Toutes</option>
          <option value="haute">Haute</option>
          <option value="moyenne">Moyenne</option>
          <option value="basse">Basse</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <p>Chargement des réclamations...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="reclamations.length === 0" class="empty-state">
      <AlertCircle :size="48" />
      <p>Aucune réclamation trouvée</p>
    </div>

    <!-- Reclamations List -->
    <div v-else class="reclamations-list">
      <div
        v-for="reclamation in reclamations"
        :key="reclamation.id"
        class="reclamation-card"
        :class="getStatusClass(reclamation.statut)"
      >
        <div class="reclamation-header">
          <div class="reclamation-info">
            <h3 class="reclamation-title">{{ reclamation.raison }}</h3>
            <p class="reclamation-concernant">
              Concernant: <strong>{{ reclamation.concernant_name }}</strong>
            </p>
            <div v-if="reclamation.intervention_id" class="reclamation-intervention-tag">
              <Calendar :size="12" />
              <span>Intervention liée #{{ reclamation.intervention_id }}</span>
            </div>
          </div>
          <div class="reclamation-badges">
            <span class="badge badge-status" :class="getStatusBadgeClass(reclamation.statut)">
              {{ formatStatus(reclamation.statut) }}
            </span>
            <span class="badge badge-priority" :class="getPriorityBadgeClass(reclamation.priorite)">
              {{ formatPriority(reclamation.priorite) }}
            </span>
          </div>
        </div>

        <div class="reclamation-body">
          <p class="reclamation-message">{{ reclamation.message }}</p>
          <div v-if="reclamation.notes_admin" class="admin-notes">
            <strong>Notes de l'administrateur:</strong>
            <p>{{ reclamation.notes_admin }}</p>
          </div>
        </div>

        <div class="reclamation-footer">
          <span class="reclamation-date">
            Créée le {{ formatDate(reclamation.created_at) }}
          </span>
          <button
            @click="viewDetails(reclamation)"
            class="btn-view-details"
          >
            Voir les détails
          </button>
        </div>
      </div>
    </div>

    <!-- Create Reclamation Modal -->
    <div v-if="showCreateModal" class="modal-overlay" @click="closeCreateModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Nouvelle Réclamation</h2>
          <button @click="closeCreateModal" class="btn-close">
            <X :size="24" />
          </button>
        </div>

        <form @submit.prevent="submitReclamation" class="reclamation-form">
          <div class="form-group">
             <label>Type de source</label>
             <input type="text" value="Intervention Terminée" disabled class="bg-gray-100" />
          </div>

          <div class="form-group">
            <label>Intervention concernée <span class="required">*</span></label>
            <select
              v-model="selectedInterventionId"
              required
              :disabled="loadingInterventions"
            >
              <option value="">Sélectionner une intervention terminée...</option>
              <option
                v-for="intervention in interventions"
                :key="intervention.id"
                :value="intervention.id"
              >
                {{ intervention.serviceName }} - {{ formatDate(intervention.date) }} ({{ intervention.providerName }})
              </option>
            </select>
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
            <button type="button" @click="closeCreateModal" class="btn-cancel">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="selectedReclamation" class="modal-overlay" @click="closeDetailsModal">
      <div class="modal-content modal-large" @click.stop>
        <div class="modal-header">
          <h2>Détails de la Réclamation</h2>
          <button @click="closeDetailsModal" class="btn-close">
            <X :size="24" />
          </button>
        </div>

        <div class="details-content">
          <div class="detail-section">
            <h3>Informations Générales</h3>
            <div class="detail-grid">
              <div class="detail-item">
                <label>Raison:</label>
                <p>{{ selectedReclamation.raison }}</p>
              </div>
              <div class="detail-item">
                <label>Statut:</label>
                <span class="badge badge-status" :class="getStatusBadgeClass(selectedReclamation.statut)">
                  {{ formatStatus(selectedReclamation.statut) }}
                </span>
              </div>
              <div class="detail-item">
                <label>Priorité:</label>
                <span class="badge badge-priority" :class="getPriorityBadgeClass(selectedReclamation.priorite)">
                  {{ formatPriority(selectedReclamation.priorite) }}
                </span>
              </div>
              <div class="detail-item">
                <label>Concernant:</label>
                <p>{{ selectedReclamation.concernant_name }}</p>
              </div>
              <div class="detail-item">
                <label>Date de création:</label>
                <p>{{ formatDate(selectedReclamation.created_at) }}</p>
              </div>
              <div class="detail-item">
                <label>Dernière mise à jour:</label>
                <p>{{ formatDate(selectedReclamation.updated_at) }}</p>
              </div>
              <div v-if="selectedReclamation.intervention_id" class="detail-item">
                <label>Intervention liée:</label>
                <p class="text-blue-600 font-bold">#{{ selectedReclamation.intervention_id }}</p>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <h3>Message</h3>
            <p class="detail-message">{{ selectedReclamation.message }}</p>
          </div>

          <div v-if="selectedReclamation.notes_admin" class="detail-section">
            <h3>Notes de l'Administrateur</h3>
            <p class="detail-notes">{{ selectedReclamation.notes_admin }}</p>
          </div>
        </div>

        <div class="modal-actions">
          <button @click="closeDetailsModal" class="btn-cancel">Fermer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Plus, X, AlertCircle, Calendar } from 'lucide-vue-next'
import clientReclamationService from '@/services/clientReclamationService'
import authService from '@/services/authService'
import profileService from '@/services/profileService'

const props = defineProps({
  // Optional: Pre-fill intervenant when opening from intervenant profile
  preselectedIntervenantId: {
    type: Number,
    default: null
  },
  // Optional: Auto-open create modal
  autoOpenCreate: {
    type: Boolean,
    default: false
  }
})

const reclamations = ref([])
const loading = ref(false)
const showCreateModal = ref(false)
const selectedReclamation = ref(null)
const submitting = ref(false)
const error = ref(null)
const loadingInterventions = ref(false)
const interventions = ref([])
const selectedInterventionId = ref('')
const currentUser = ref(null)

const filters = ref({
  statut: 'all',
  priorite: 'all'
})

const form = ref({
  concernant_type: 'Intervenant',
  concernant_id: '',
  raison: '',
  message: '',
  priorite: 'moyenne'
})

// Watch for preselected intervenant
watch(() => props.preselectedIntervenantId, (newId) => {
  if (newId && interventions.value.length > 0) {
    const matchingIntervention = interventions.value.find(i => 
      (i.providerId === newId || i.intervenant_id === newId)
    )
    if (matchingIntervention) {
      selectedInterventionId.value = matchingIntervention.id
    }
  }
}, { immediate: true })

// Also watch interventions for when they load
watch(interventions, (newVal) => {
  if (props.preselectedIntervenantId && newVal.length > 0) {
    const matchingIntervention = newVal.find(i => 
      (i.providerId === props.preselectedIntervenantId || i.intervenant_id === props.preselectedIntervenantId)
    )
    if (matchingIntervention) {
      selectedInterventionId.value = matchingIntervention.id
    }
  }
})

// Watch for auto-open
watch(() => props.autoOpenCreate, (shouldOpen) => {
  if (shouldOpen) {
    showCreateModal.value = true
  }
}, { immediate: true })

onMounted(async () => {
  await loadCurrentUser()
  await loadReclamations()
  if (currentUser.value) {
    await loadHistory()
  }
})

async function loadCurrentUser() {
  try {
     const res = await authService.getCurrentUser()
     currentUser.value = res.data
  } catch (e) {
     console.error("Error loading user", e)
  }
}

async function loadReclamations() {
  loading.value = true
  error.value = null
  try {
    const params = {}
    if (filters.value.statut !== 'all') {
      params.statut = filters.value.statut
    }
    if (filters.value.priorite !== 'all') {
      params.priorite = filters.value.priorite
    }
    
    const response = await clientReclamationService.getAll(params)
    reclamations.value = response.data || []
  } catch (err) {
    console.error('Error loading reclamations:', err)
    error.value = 'Erreur lors du chargement des réclamations'
  } finally {
    loading.value = false
  }
}

async function loadHistory() {
  if (!currentUser.value) return
  
  loadingInterventions.value = true
  try {
    const clientId = currentUser.value.client?.id || currentUser.value.id
    const response = await profileService.getHistory(clientId)
    interventions.value = response.data.data || []
  } catch (err) {
    console.error('Error loading history:', err)
  } finally {
    loadingInterventions.value = false
  }
}

async function submitReclamation() {
  if (!selectedInterventionId.value || !form.value.raison || !form.value.message) {
    error.value = 'Veuillez remplir tous les champs obligatoires'
    return
  }

  const selectedIntervention = interventions.value.find(i => i.id === selectedInterventionId.value)
  if (!selectedIntervention) {
     error.value = "Intervention invalide"
     return
  }

  // Enrich message with intervention context
  const contextMessage = `[Intervention #${selectedIntervention.id} - ${selectedIntervention.serviceName} du ${formatDate(selectedIntervention.date)}] \n\n${form.value.message}`

  submitting.value = true
  error.value = null

  try {
    // We link implicitly to the intervenant of the intervention
    // Assuming selectedIntervention has providerId or we infer it
    const providerId = selectedIntervention.providerId || selectedIntervention.intervenant_id
    
    await clientReclamationService.create({
      concernant_id: parseInt(providerId),
      concernant_type: 'Intervenant',
      intervention_id: selectedIntervention.id,
      raison: form.value.raison,
      message: form.value.message,
      priorite: form.value.priorite
    })

    // Reset form
    form.value = {
      concernant_type: 'Intervenant',
      concernant_id: '',
      raison: '',
      message: '',
      priorite: 'moyenne'
    }
    selectedInterventionId.value = ''

    showCreateModal.value = false
    await loadReclamations()
    
    alert('Réclamation créée avec succès!')
  } catch (err) {
    console.error('Error creating reclamation:', err)
    error.value = err.response?.data?.message || 'Erreur lors de la création de la réclamation'
  } finally {
    submitting.value = false
  }
}

function viewDetails(reclamation) {
  selectedReclamation.value = reclamation
}

function closeCreateModal() {
  showCreateModal.value = false
  error.value = null
  form.value = {
    concernant_type: 'Intervenant',
    concernant_id: '',
    raison: '',
    message: '',
    priorite: 'moyenne'
  }
}

function closeDetailsModal() {
  selectedReclamation.value = null
}

function formatStatus(status) {
  const statusMap = {
    'en_attente': 'En attente',
    'en_cours': 'En cours',
    'resolu': 'Résolu'
  }
  return statusMap[status] || status
}

function formatPriority(priority) {
  const priorityMap = {
    'haute': 'Haute',
    'moyenne': 'Moyenne',
    'basse': 'Basse'
  }
  return priorityMap[priority] || priority
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getStatusClass(status) {
  return {
    'status-pending': status === 'en_attente',
    'status-in-progress': status === 'en_cours',
    'status-resolved': status === 'resolu'
  }
}

function getStatusBadgeClass(status) {
  return {
    'badge-pending': status === 'en_attente',
    'badge-in-progress': status === 'en_cours',
    'badge-resolved': status === 'resolu'
  }
}

function getPriorityBadgeClass(priority) {
  return {
    'badge-high': priority === 'haute',
    'badge-medium': priority === 'moyenne',
    'badge-low': priority === 'basse'
  }
}
</script>

<style scoped>
.client-reclamations-tab {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.tab-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.tab-title {
  font-size: 28px;
  font-weight: bold;
  color: #2F4F4F;
}

.btn-create {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #92B08B;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.3s;
}

.btn-create:hover {
  background: #7a9a73;
}

.filters-section {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  padding: 16px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-group label {
  font-weight: 500;
  color: #2F4F4F;
}

.filter-group select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 48px;
  color: #666;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.reclamations-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.reclamation-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  border-left: 4px solid #ddd;
  transition: transform 0.2s, box-shadow 0.2s;
}

.reclamation-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.reclamation-card.status-pending {
  border-left-color: #FF9800;
}

.reclamation-card.status-in-progress {
  border-left-color: #2196F3;
}

.reclamation-card.status-resolved {
  border-left-color: #4CAF50;
}

.reclamation-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.reclamation-info {
  flex: 1;
}

.reclamation-title {
  font-size: 20px;
  font-weight: bold;
  color: #2F4F4F;
  margin-bottom: 8px;
}

.reclamation-concernant {
  color: #666;
  font-size: 14px;
  margin-bottom: 4px;
}

.reclamation-intervention-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  background: #E3F2FD;
  color: #1976D2;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
}

.reclamation-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge-status {
  background: #f0f0f0;
  color: #666;
}

.badge-status.badge-pending {
  background: #FFF3E0;
  color: #F57C00;
}

.badge-status.badge-in-progress {
  background: #E3F2FD;
  color: #1976D2;
}

.badge-status.badge-resolved {
  background: #E8F5E9;
  color: #388E3C;
}

.badge-priority {
  background: #f0f0f0;
  color: #666;
}

.badge-priority.badge-high {
  background: #FFEBEE;
  color: #C62828;
}

.badge-priority.badge-medium {
  background: #FFF9C4;
  color: #F57F17;
}

.badge-priority.badge-low {
  background: #E8F5E9;
  color: #2E7D32;
}

.reclamation-body {
  margin-bottom: 16px;
}

.reclamation-message {
  color: #555;
  line-height: 1.6;
  margin-bottom: 12px;
}

.admin-notes {
  padding: 12px;
  background: #F5F5F5;
  border-radius: 6px;
  margin-top: 12px;
}

.admin-notes strong {
  color: #2F4F4F;
  display: block;
  margin-bottom: 4px;
}

.reclamation-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.reclamation-date {
  color: #999;
  font-size: 14px;
}

.btn-view-details {
  padding: 8px 16px;
  background: #92B08B;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.3s;
}

.btn-view-details:hover {
  background: #7a9a73;
}

/* Modal Styles */
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

.modal-large {
  max-width: 800px;
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

.details-content {
  padding: 24px;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-section h3 {
  font-size: 18px;
  color: #2F4F4F;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 2px solid #92B08B;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item label {
  font-weight: 600;
  color: #666;
  font-size: 14px;
}

.detail-item p {
  color: #2F4F4F;
  margin: 0;
}

.detail-message,
.detail-notes {
  padding: 16px;
  background: #F5F5F5;
  border-radius: 6px;
  line-height: 1.6;
  color: #555;
}

.detail-notes {
  background: #E3F2FD;
  color: #1976D2;
}
</style>

