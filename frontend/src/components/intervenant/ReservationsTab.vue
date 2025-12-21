<template>
  <div class="container">
    <!-- Error Message -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <p>Chargement des réservations...</p>
    </div>

    <!-- Stats -->
    <div v-if="!loading" class="stats-grid">
      <div class="stat-card stat-orange">
        <p class="stat-label">En Attente</p>
        <p class="stat-value">{{ pendingCount }}</p>
      </div>
      <div class="stat-card stat-green">
        <p class="stat-label">Acceptées</p>
        <p class="stat-value">{{ acceptedCount }}</p>
      </div>
      <div class="stat-card stat-blue">
        <p class="stat-label">Complétées</p>
        <p class="stat-value">{{ completedCount }}</p>
      </div>
    </div>

    <!-- Reservations Card -->
    <div v-if="!loading" class="card">
      <h1>Gérer mes réservations</h1>
      <p class="subtitle">Acceptez ou refusez les demandes de réservation de vos clients</p>

      <!-- Tabs -->
      <div class="tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="selectedTab = tab.id"
          class="tab-btn"
          :class="{ 'tab-active': selectedTab === tab.id }"
          :style="selectedTab === tab.id ? {
            backgroundColor: tab.color,
            color: 'white'
          } : {}"
        >
          {{ tab.label }} ({{ tab.count }})
        </button>
      </div>

      <!-- Service Filter Bar -->
      <div class="filter-bar" v-if="allServices.length > 0">
        <div class="filter-label">
          <Package :size="16" />
          <span>Filtrer par service :</span>
        </div>
        <div class="filter-options">
          <button 
            @click="selectedService = 'all'"
            class="filter-chip"
            :class="{ 'filter-chip-active': selectedService === 'all' }"
          >
            Tous les services
          </button>
          <button 
            v-for="service in allServices"
            :key="service.id"
            @click="selectedService = service.name"
            class="filter-chip"
            :class="{ 'filter-chip-active': selectedService === service.name }"
          >
            {{ service.name }}
          </button>
        </div>
      </div>

      <!-- Reservations List -->
      <TransitionGroup name="list" tag="div" class="reservations-list">
        <div
          v-for="reservation in filteredReservations"
          :key="reservation.id"
          class="reservation-card"
        >
          <div class="reservation-content">
            <!-- Client Info -->
            <div class="client-info" @click="openClientProfile(reservation)" title="Voir le profil du client">
              <img :src="reservation.clientImage" :alt="reservation.clientName" class="client-avatar clickable-avatar" />
              <div>
                <h4 class="clickable-name">{{ reservation.clientName }}</h4>
                <div class="reservation-meta">
                  <span class="service-tag">{{ reservation.service }}</span>
                  <span class="separator">•</span>
                  <span class="task-name">{{ reservation.task }}</span>
                </div>
              </div>
            </div>

            <div class="reservation-details">
              <div class="reservation-header">
                <!-- Actions for Pending -->
                <div v-if="selectedTab === 'pending'" class="action-buttons">
                  <button @click="acceptReservation(reservation.id)" class="accept-btn">
                    <Check :size="18" />
                    Accepter
                  </button>
                  <button @click="refuseReservation(reservation.id)" class="refuse-btn">
                    <X :size="18" />
                    Refuser
                  </button>
                </div>

                <!-- Status Badges and Actions -->
                <div v-else-if="selectedTab === 'completed'" class="completed-actions">
                  <span class="status-badge status-completed">
                    Terminée
                  </span>
                  <button @click="generateInvoice(reservation.id)" class="invoice-btn">
                    <FileText :size="16" />
                    Facture
                  </button>

                  <!-- View both evaluations (public) - HIGHEST PRIORITY -->
                  <button 
                    v-if="reservation.canViewPublic" 
                    @click="openPublicEvaluations(reservation)" 
                    class="rating-btn rating-btn-public"
                  >
                    <Star :size="16" />
                    Voir les évaluations
                  </button>
                  
                  <!-- Can rate -->
                  <button 
                    v-else-if="reservation.evaluationStatus === 'can_rate'" 
                    @click="openRatingModal(reservation)" 
                    class="rating-btn"
                  >
                    <Star :size="16" />
                    Évaluer
                  </button>
                  
                  <!-- View own evaluation (waiting for client) -->
                  <button 
                    v-else-if="reservation.evaluationStatus === 'view_only'" 
                    @click="openRatingModal(reservation)" 
                    class="rating-btn"
                  >
                    <Star :size="16" />
                    Voir mon évaluation
                  </button>
                  
                  <!-- Expired -->
                  <span v-else-if="reservation.evaluationStatus === 'expired'" class="expired-notice">
                    Période expirée
                  </span>
                </div>
                
                <!-- Complaint Button - Always available for completed interventions -->
                <div v-if="selectedTab === 'completed'" class="completed-actions">
                  <button 
                    @click="openComplaintModal(reservation)" 
                    class="complaint-btn"
                  >
                    <Flag :size="16" />
                    Réclamer
                  </button>
                </div>
                <div v-else-if="selectedTab === 'accepted'" class="accepted-actions">
                  <span class="status-badge status-accepted">
                    Confirmée
                  </span>
                  <button @click="generateInvoice(reservation.id)" class="invoice-btn">
                    <FileText :size="16" />
                    Facture
                  </button>
                </div>
                <span v-else-if="selectedTab === 'refused'" class="status-badge status-refused">
                  Refusée
                </span>
              </div>

              <!-- Details Grid -->
              <div class="details-grid">
                <div class="detail-item">
                  <Calendar :size="18" class="icon-blue" />
                  <span>{{ formatDate(reservation.date) }}</span>
                </div>
                <div class="detail-item">
                  <Clock :size="18" class="icon-green" />
                  <span>{{ reservation.time }} • {{ reservation.duration }}</span>
                </div>
                <div class="detail-item">
                  <MapPin :size="18" class="icon-red" />
                  <span>{{ reservation.location }}</span>
                </div>
                <div class="detail-item">
                  <Coins :size="18" class="icon-yellow" />
                  <span class="price">{{ calculateTotal(reservation) }} DH</span>
                  <span class="price-detail">({{ reservation.hourlyRate }} DH/h)</span>
                </div>
              </div>

              <!-- Message -->
              <div v-if="reservation.message" class="message-box">
                <MessageSquare :size="18" />
                <div>
                  <p class="message-label">Message du client :</p>
                  <p class="message-text">{{ reservation.message }}</p>
                </div>
              </div>

              <!-- Materials Section -->
              <div v-if="reservation.materials && reservation.materials.length > 0" class="materials-section">
                <div class="materials-header">
                  <Package :size="18" />
                  <p class="materials-title">Matériels nécessaires</p>
                </div>
                
                <!-- Client Provided Materials -->
                <div v-if="reservation.clientProvidedMaterials && reservation.clientProvidedMaterials.length > 0" class="materials-group">
                  <p class="materials-subtitle">Fournis par le client :</p>
                  <div class="materials-tags">
                    <span v-for="material in reservation.clientProvidedMaterials" :key="material.id" class="material-tag client-tag">
                      {{ material.name }}
                    </span>
                  </div>
                </div>

                <!-- Intervenant Materials -->
                <div v-if="reservation.intervenantMaterials && reservation.intervenantMaterials.length > 0" class="materials-group">
                  <p class="materials-subtitle">Matériels à apporter :</p>
                  <div class="materials-tags">
                    <span v-for="material in reservation.intervenantMaterials" :key="material.id" class="material-tag intervenant-tag">
                      {{ material.name }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Card Footer with Details Button -->
              <div class="card-footer">
                <button @click="openInterventionDetails(reservation.id)" class="details-link-btn">
                  Plus de détails
                </button>
              </div>
            </div>
          </div>
        </div>
      </TransitionGroup>

      <div v-if="filteredReservations.length === 0" class="empty-state">
        <p>Aucune réservation {{ 
          selectedTab === 'pending' ? 'en attente' : 
          selectedTab === 'accepted' ? 'acceptée' : 
          selectedTab === 'completed' ? 'complétée' : 
          'refusée' 
        }}</p>
      </div>
    </div>

    <!-- Client Rating Modal -->
    <ClientRatingModal
      :show="showRatingModal"
      :reservation="selectedReservation"
      @close="closeRatingModal"
      @rating-submitted="onRatingSubmitted"
    />
    
    <!-- Complaint Modal -->
    <div v-if="showComplaintModal" class="modal-overlay" @click="closeComplaintModal">
      <div class="complaint-modal-content" @click.stop>
        <div class="complaint-modal-header">
          <h2 class="complaint-modal-title">
            <Flag :size="20" />
            Réclamation sur intervention
          </h2>
          <button @click="closeComplaintModal" class="modal-close-btn">×</button>
        </div>
        
        <div class="complaint-modal-body">
          <!-- Intervention Information -->
          <div class="intervention-info">
            <h4 class="info-title">Informations de l'intervention</h4>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Service :</span>
                <span class="info-value">{{ selectedReservation?.service || '-' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Sous-service :</span>
                <span class="info-value">{{ selectedReservation?.task || '-' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Client concerné :</span>
                <span class="info-value">{{ selectedReservation?.clientName || '-' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Date :</span>
                <span class="info-value">{{ selectedReservation ? formatDate(selectedReservation.date) : '-' }}</span>
              </div>
            </div>
          </div>
          
          <div class="complaint-form">
            <div class="form-group">
              <label class="form-label">Raison de la réclamation *</label>
              <input 
                v-model="complaintForm.raison" 
                type="text" 
                class="form-input"
                placeholder="Ex: Problème de paiement, comportement inapproprié..."
                required
              />
            </div>
            
            <div class="form-group">
              <label class="form-label">Priorité *</label>
              <select 
                v-model="complaintForm.priorite" 
                class="form-select"
                required
              >
                <option value="">Sélectionner une priorité</option>
                <option value="basse">Basse</option>
                <option value="moyenne">Moyenne</option>
                <option value="haute">Haute</option>
              </select>
            </div>
            
            <div class="form-group">
              <label class="form-label">Sujet de la réclamation *</label>
              <textarea 
                v-model="complaintForm.sujet" 
                class="form-textarea"
                rows="4"
                placeholder="Décrivez en détail le problème rencontré..."
                required
              ></textarea>
            </div>
          </div>
        </div>
        
        <div class="complaint-modal-footer">
          <button 
            @click="closeComplaintModal" 
            class="btn-cancel"
            :disabled="isSubmittingComplaint"
          >
            Annuler
          </button>
          <button 
            @click="submitComplaint" 
            class="btn-submit"
            :disabled="isSubmittingComplaint || !isComplaintFormValid"
          >
            <span v-if="isSubmittingComplaint" class="spinner"></span>
            {{ isSubmittingComplaint ? 'Envoi en cours...' : 'Envoyer la réclamation' }}
          </button>
        </div>
      </div>
    </div>
    
    <!-- Client Profile Modal -->
    <ClientProfileModal
      :show="showClientProfile"
      :clientId="selectedClientId"
      @close="closeClientProfile"
      @view-evaluation="handleViewEvaluation"
    />
    
    <!-- Public Evaluations Modal -->
    <div v-if="showPublicModal" class="modal-overlay" @click="closePublicModal">
      <div class="public-modal-content" @click.stop>
        <div class="public-modal-body" v-if="selectedReservation?.publicData">
          <h2 class="public-modal-title">Évaluations Mutuelles</h2>
          
          <div class="evaluations-grid">
            <!-- Intervenant Evaluation -->
            <div class="evaluation-section">
              <h3>Votre évaluation du client</h3>
              <div v-if="selectedReservation.publicData.intervenant_ratings.length > 0">
                <div v-for="rating in selectedReservation.publicData.intervenant_ratings" :key="rating.id" class="rating-item">
                  <span class="criteria-name">{{ rating.critaire.nom_critaire }}</span>
                  <div class="stars">
                    <Star v-for="n in 5" :key="n" :size="24" :fill="n <= rating.note ? 'currentColor' : 'none'" class="star" :class="{ 'star-filled': n <= rating.note }" />
                  </div>
                </div>
                <div class="comment-box">
                  <strong>Commentaire personnel</strong>
                  <p v-if="selectedReservation.publicData.intervenant_comment">{{ selectedReservation.publicData.intervenant_comment.commentaire }}</p>
                  <p v-else class="no-comment">Aucun commentaire</p>
                </div>
              </div>
              <p v-else class="no-evaluation">Pas d'évaluation</p>
            </div>
            
            <!-- Client Evaluation -->
            <div class="evaluation-section">
              <h3>Évaluation du client</h3>
              <div v-if="selectedReservation.publicData.client_ratings.length > 0">
                <div v-for="rating in selectedReservation.publicData.client_ratings" :key="rating.id" class="rating-item">
                  <span class="criteria-name">{{ rating.critaire.nom_critaire }}</span>
                  <div class="stars">
                    <Star v-for="n in 5" :key="n" :size="24" :fill="n <= rating.note ? 'currentColor' : 'none'" class="star" :class="{ 'star-filled': n <= rating.note }" />
                  </div>
                </div>
                <div class="comment-box">
                  <strong>Commentaire personnel</strong>
                  <p v-if="selectedReservation.publicData.client_comment">{{ selectedReservation.publicData.client_comment.commentaire }}</p>
                  <p v-else class="no-comment">Aucun commentaire</p>
                </div>
              </div>
              <p v-else class="no-evaluation">Pas d'évaluation</p>
            </div>
          </div>
          
          <div class="visibility-info">
            <p v-if="selectedReservation.publicData.both_parties_voted">
              Les deux parties ont évalué
            </p>
            <p v-else-if="selectedReservation.publicData.window_passed">
              Délai de 7 jours expiré
            </p>
          </div>
          
          <div class="public-modal-actions">
            <button @click="closePublicModal" class="close-modal-btn">Fermer</button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredReservations.length === 0" class="empty-state">
          <div class="empty-icon">
            <Clock :size="48" v-if="selectedTab === 'pending'" />
            <Calendar :size="48" v-else />
          </div>
          <h3>Aucune réservation trouvée</h3>
          <p v-if="selectedService !== 'all'">
            Il n'y a pas de demandes pour le service <strong>"{{ selectedService }}"</strong> dans cette catégorie.
          </p>
          <p v-else>
            Vous n'avez pas encore de réservations dans la catégorie {{ tabs.find(t => t.id === selectedTab)?.label }}.
          </p>
        </div>
      </div>
    </div>

    <!-- Intervention Details Modal -->
    <InterventionDetailsModal
      :show="showDetailsModal"
      :intervention-id="selectedInterventionId"
      @close="closeInterventionDetails"
    />

    <!-- Small Mail Notification -->
    <Transition name="fade-slide">
      <div v-if="notification" class="mail-notification">
        <div class="notif-icon">
          <Mail :size="20" />
        </div>
        <div class="notif-content">
          <p class="notif-msg">{{ notification }}</p>
        </div>
        <button @click="notification = null" class="notif-close">×</button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Check, X, Clock, MapPin, Calendar, MessageSquare, Coins, Package, Star, Mail, AlertTriangle, FileText, Flag } from 'lucide-vue-next'
// ... existing imports ...

const showRefuseModal = ref(false)
const refusalId = ref(null)
// ... existing refs ...

const refuseReservation = (id) => {
  refusalId.value = id
  showRefuseModal.value = true
}

const confirmRefusal = async () => {
  if (!refusalId.value) return

  try {
    await reservationService.refuseReservation(refusalId.value)

    // Optimistic Update
    const index = reservations.value.findIndex(r => r.id === refusalId.value)
    if (index !== -1) {
      reservations.value[index].status = 'refused'
    }
  } catch (err) {
    alert(err.message || 'Erreur lors du refus de la réservation')
    console.error(err)
  } finally {
    closeRefuseModal()
  }
}

const closeRefuseModal = () => {
  showRefuseModal.value = false
  refusalId.value = null
}

import reservationService from '@/services/intervenantReservationService'
import evaluationService from '@/services/evaluationService'
import api from '@/services/api'
import ClientRatingModal from './ClientRatingModal.vue'
import ClientProfileModal from './ClientProfileModal.vue'
import InterventionDetailsModal from './InterventionDetailsModal.vue'

const selectedTab = ref('pending')
const reservations = ref([])
const loading = ref(false)
const error = ref(null)
const showRatingModal = ref(false)
const selectedReservation = ref(null)
const showComplaintModal = ref(false)
const isSubmittingComplaint = ref(false)
const complaintForm = ref({
  raison: '',
  priorite: '',
  sujet: '',
  intervention_id: null
})
const showPublicModal = ref(false)
const showClientProfile = ref(false)
const selectedClientId = ref(null)
const selectedService = ref('all')
const allServices = ref([])
const showDetailsModal = ref(false)
const selectedInterventionId = ref(null)
const notification = ref(null)

const fetchAllServices = async () => {
  try {
    const response = await api.get('services')
    allServices.value = response.data.data || response.data.services || []
  } catch (err) {
    console.error('Error fetching global services:', err)
  }
}

// Keep availableServices if needed for internal logic or delete if replaced
// I will keep allServices as the primary source for the UI buttons now.

const filteredReservations = computed(() => {
  return reservations.value.filter(r => {
    const matchesTab = r.status === selectedTab.value
    const matchesService = selectedService.value === 'all' || r.service === selectedService.value
    return matchesTab && matchesService
  })
})

const pendingCount = computed(() => reservations.value.filter(r => r.status === 'pending').length)
const acceptedCount = computed(() => reservations.value.filter(r => r.status === 'accepted').length)
const completedCount = computed(() => reservations.value.filter(r => r.status === 'completed').length)
const refusedCount = computed(() => reservations.value.filter(r => r.status === 'refused').length)

const tabs = computed(() => [
  { id: 'pending', label: 'En Attente', color: '#E8793F', count: pendingCount.value },
  { id: 'accepted', label: 'Acceptées', color: '#92B08B', count: acceptedCount.value },
  { id: 'completed', label: 'Complétées', color: '#4682B4', count: completedCount.value },
  { id: 'refused', label: 'Refusées', color: '#EF4444', count: refusedCount.value },
])

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long'
  })
}

const calculateTotal = (reservation) => {
  const hours = parseInt(reservation.duration)
  return reservation.hourlyRate * hours
}

const fetchReservations = async (silent = false) => {
  if (!silent) loading.value = true
  // Don't clear error on silent refresh to avoid flickering error messages
  if (!silent) error.value = null

  try {
    const response = await reservationService.getMyReservations()
    const fetchedReservations = response.reservations || []
    
    // Check evaluation status for completed reservations
    for (const reservation of fetchedReservations) {
      if (reservation.status === 'completed') {
        try {
          const statusData = await evaluationService.canRateClient(reservation.id)
          reservation.evaluationStatus = statusData.status
          reservation.canViewPublic = statusData.is_public === true
        } catch (err) {
          console.error('Error checking evaluation status:', err)
          reservation.evaluationStatus = 'unknown'
          reservation.canViewPublic = false
        }
      }
    }
    
    reservations.value = fetchedReservations
  } catch (err) {
    if (!silent) error.value = err.message || 'Erreur lors du chargement des réservations'
    console.error(err)
  } finally {
    if (!silent) loading.value = false
  }
}

const generateInvoice = async (id) => {
  try {
    const data = await reservationService.generateInvoice(id)
    if (data.url) {
      window.open(data.url, '_blank')
    } else {
      alert('Erreur: URL de facture manquante')
    }
  } catch (err) {
    alert(err.message || 'Erreur lors de la génération de la facture')
    console.error(err)
  }
}

const acceptReservation = async (id) => {
  try {
    const response = await api.post(`intervenants/me/reservations/${id}/accept`)

    // Optimistic Update: Update status locally without refresh
    const index = reservations.value.findIndex(r => r.id === id)
    if (index !== -1) {
      reservations.value[index].status = 'accepted'
    }

    // Show notification
    if (response.data.message) {
      notification.value = response.data.message
      setTimeout(() => {
        notification.value = null
      }, 5000)
    }
  } catch (err) {
    alert(err.message || 'Erreur lors de l\'acceptation de la réservation')
    console.error(err)
  }
}



const openRatingModal = async (reservation) => {
  try {
    console.log('Checking rating permissions for intervention:', reservation.id)
    console.log('Reservation data:', reservation)
    
    // First check current auth status
    const authResponse = await api.get('debug/auth')
    console.log('Current auth status:', authResponse.data)
    
    // Then check specific intervention
    const interventionResponse = await api.get(`debug/intervention/${reservation.id}`)
    console.log('Intervention debug:', interventionResponse.data)
    
    const data = await evaluationService.canRateClient(reservation.id)
    console.log('Rating permission response:', data)
    
    // Allow modal to open for both can_rate and can_view scenarios
    if (data.can_rate || data.can_view) {
      selectedReservation.value = reservation
      showRatingModal.value = true
    } else {
      alert(data.reason || 'Vous ne pouvez pas évaluer ce client')
    }
  } catch (err) {
    console.error('Rating permission error:', err)
    const errorMessage = err.response?.data?.reason || 
                        err.response?.data?.message || 
                        err.message || 
                        'Erreur lors de la vérification des permissions d\'évaluation'
    alert(errorMessage)
  }
}

const closeRatingModal = () => {
  showRatingModal.value = false
  selectedReservation.value = null
}

const openComplaintModal = (reservation) => {
  selectedReservation.value = reservation
  complaintForm.value = {
    raison: '',
    priorite: '',
    sujet: '',
    intervention_id: reservation.id
  }
  showComplaintModal.value = true
}

const closeComplaintModal = () => {
  showComplaintModal.value = false
  selectedReservation.value = null
  complaintForm.value = {
    raison: '',
    priorite: '',
    sujet: '',
    intervention_id: null
  }
}

const isComplaintFormValid = computed(() => {
  return complaintForm.value.raison.trim() !== '' && 
         complaintForm.value.priorite !== '' && 
         complaintForm.value.sujet.trim() !== ''
})

const submitComplaint = async () => {
  if (!isComplaintFormValid.value) return
  
  isSubmittingComplaint.value = true
  
  try {
    const response = await api.post('/reclamations', {
      intervention_id: complaintForm.value.intervention_id,
      raison: complaintForm.value.raison,
      priorite: complaintForm.value.priorite,
      message: complaintForm.value.sujet
    })
    
    notification.value = 'Réclamation envoyée avec succès!'
    setTimeout(() => {
      notification.value = null
    }, 3000)
    closeComplaintModal()
    
  } catch (error) {
    console.error('Error submitting complaint:', error)
    notification.value = 'Erreur lors de l\'envoi de la réclamation'
    setTimeout(() => {
      notification.value = null
    }, 3000)
  } finally {
    isSubmittingComplaint.value = false
  }
}

const openPublicEvaluations = async (reservation) => {
  try {
    console.log('Opening public evaluations for reservation:', reservation.id)
    const data = await evaluationService.getPublicEvaluations(reservation.id)
    console.log('Public evaluations data received:', data)
    console.log('Intervenant ratings:', data.intervenant_ratings)
    console.log('Client ratings:', data.client_ratings)
    
    if (data.can_view) {
      selectedReservation.value = {
        ...reservation,
        publicData: data
      }
      console.log('Setting selectedReservation with publicData:', selectedReservation.value)
      showPublicModal.value = true
    } else {
      alert('Les évaluations ne sont pas encore disponibles')
    }
  } catch (err) {
    console.error('Error loading public evaluations:', err)
    console.error('Error response:', err.response?.data)
    alert(err.response?.data?.message || 'Erreur lors du chargement des évaluations')
  }
}

const closePublicModal = () => {
  showPublicModal.value = false
  selectedReservation.value = null
}

const openClientProfile = (reservation) => {
  selectedClientId.value = reservation.id
  showClientProfile.value = true
}

const openInterventionDetails = (id) => {
  selectedInterventionId.value = id
  showDetailsModal.value = true
}

const closeInterventionDetails = () => {
  showDetailsModal.value = false
  selectedInterventionId.value = null
}

const closeClientProfile = () => {
  showClientProfile.value = false
  selectedClientId.value = null
}

const handleViewEvaluation = (interventionId) => {
  // Find the reservation for this intervention
  const reservation = reservations.value.find(r => r.id === interventionId)
  if (reservation) {
    closeClientProfile()
    openRatingModal(reservation)
  }
}

const onRatingSubmitted = async () => {
  await fetchReservations()
}

const pollInterval = ref(null)

onMounted(async () => {
  await Promise.all([
    fetchReservations(),
    fetchAllServices()
  ])

  // Poll for updates every 30 seconds (Silent Refresh)
  pollInterval.value = setInterval(() => {
    fetchReservations(true)
  }, 30000)
})

import { onUnmounted } from 'vue' // Ensure this is imported
onUnmounted(() => {
  if (pollInterval.value) clearInterval(pollInterval.value)
})
</script>

<style scoped>
.container {
  max-width: 80rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-6);
}

.stat-card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--spacing-4);
  border-left: 4px solid;
}

.stat-orange { border-left-color: var(--color-orange); }
.stat-green { border-left-color: var(--color-primary-green); }
.stat-blue { border-left-color: var(--color-blue); }

.stat-label {
  font-size: 0.75rem;
  color: var(--color-gray-600);
  margin: 0 0 var(--spacing-1) 0;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.stat-orange .stat-value { color: var(--color-orange); }
.stat-green .stat-value { color: var(--color-primary-green); }
.stat-blue .stat-value { color: var(--color-blue); }

.card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--spacing-6);
}

.card h1 {
  margin: 0 0 var(--spacing-1) 0;
  color: var(--color-dark);
}

.subtitle {
  font-size: 0.875rem;
  color: var(--color-gray-600);
  margin: 0 0 var(--spacing-6) 0;
}

.tabs {
  display: flex;
  gap: var(--spacing-2);
  padding: var(--spacing-1);
  background: var(--color-gray-100);
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-6);
}

.tab-btn {
  flex: 1;
  padding: var(--spacing-2) var(--spacing-4);
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  color: var(--color-gray-600);
  background: transparent;
  transition: all 0.2s;
}

.tab-active {
  box-shadow: var(--shadow-sm);
}

.reservations-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
  position: relative;
}

/* Filter Bar styles */
.filter-bar {
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-6);
  padding: var(--spacing-3) var(--spacing-4);
  background: white;
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-gray-200);
}

.filter-label {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  color: var(--color-gray-600);
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
}

.filter-options {
  display: flex;
  gap: var(--spacing-2);
  flex-wrap: wrap;
}

.filter-chip {
  padding: var(--spacing-1) var(--spacing-3);
  border-radius: 9999px;
  border: 1.5px solid var(--color-gray-200);
  background: white;
  color: var(--color-gray-700);
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.filter-chip:hover {
  border-color: #D1D5DB;
  background: #F9FAFB;
  color: #374151;
}

.filter-chip-active {
  background: #E8793F;
  border-color: #E8793F;
  color: white;
  box-shadow: 0 1px 3px 0 rgba(232, 121, 63, 0.4);
}

.filter-chip-active:hover {
  background: #D16834;
  border-color: #D16834;
  color: white;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
  background: white;
  border-radius: var(--radius-lg);
  border: 2px dashed var(--color-gray-200);
}

.empty-icon {
  color: var(--color-gray-300);
  margin-bottom: 1.5rem;
}

.empty-state h3 {
  color: var(--color-gray-700);
  margin-bottom: 0.5rem;
  font-size: 1.25rem;
}

.empty-state p {
  color: var(--color-gray-500);
  max-width: 300px;
}

.empty-state strong {
  color: #E8793F;
}

/* Card Footer & Details Button */
.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: var(--spacing-4);
  padding-top: var(--spacing-4);
  border-top: 1px solid var(--color-gray-100);
}

.details-link-btn {
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  color: #4B5563;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.details-link-btn:hover {
  background: #FFFFFF;
  border-color: #D1D5DB;
  color: #111827;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* List Transitions */
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* Ensure leaving items are taken out of layout flow so others can move up */
.list-leave-active {
  position: absolute;
  width: 100%; /* Maintain width during leave */
}

.details-link-btn::after {
  content: "→";
  font-size: 1rem;
  transition: transform 0.2s;
  color: #E8793F;
}

.details-link-btn:hover::after {
  transform: translateX(2px);
}

/* Clickable Client Info */
.client-info {
  cursor: pointer;
  transition: opacity 0.2s;
  display: inline-block;
}

.client-info:hover {
  opacity: 0.8;
}

.clickable-avatar {
  transition: transform 0.2s;
}

.clickable-avatar:hover {
  transform: scale(1.05);
}

.clickable-name {
  color: var(--color-dark);
  transition: color 0.2s;
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.clickable-name:hover {
  color: #E8793F;
}

.reservation-card {
  border: 2px solid var(--color-gray-200);
  border-radius: var(--radius-xl);
  padding: var(--spacing-6);
  transition: box-shadow 0.2s;
}

.reservation-card:hover {
  box-shadow: var(--shadow-md);
}

.reservation-content {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-6);
}

.client-avatar {
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.reservation-details {
  flex: 1;
}

.reservation-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: var(--spacing-4);
}

.reservation-header h3 {
  font-size: 1.25rem;
  margin: 0 0 var(--spacing-1) 0;
  color: var(--color-dark);
}

.task-name {
  color: var(--color-gray-600);
  margin: 0;
}

.action-buttons {
  display: flex;
  gap: var(--spacing-2);
}

.accept-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  padding: var(--spacing-2) var(--spacing-4);
  background-color: #16A34A;
  color: var(--color-white);
  border-radius: var(--radius-lg);
  transition: background-color 0.2s;
}

.accept-btn:hover {
  background-color: #15803D;
}

.refuse-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  padding: var(--spacing-2) var(--spacing-4);
  background-color: #DC2626;
  color: var(--color-white);
  border-radius: var(--radius-lg);
  transition: background-color 0.2s;
}

.refuse-btn:hover {
  background-color: #B91C1C;
}

.status-badge {
  padding: var(--spacing-2) var(--spacing-4);
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  font-weight: 500;
}

.status-accepted {
  background-color: #D1FAE5;
  color: #065F46;
}

.status-completed {
  background-color: #DBEAFE;
  color: #1E40AF;
}

.status-refused {
  background-color: #FEE2E2;
  color: #991B1B;
}

.completed-actions {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
}

.rating-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-1);
  padding: var(--spacing-2) var(--spacing-3);
  background-color: var(--color-orange);
  color: var(--color-white);
  border: none;
  border-radius: var(--radius-lg);
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.rating-btn:hover {
  background-color: #DC2626;
}

.complaint-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-1);
  padding: var(--spacing-2) var(--spacing-3);
  background-color: #EF4444;
  color: var(--color-white);
  border: none;
  border-radius: var(--radius-lg);
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.complaint-btn:hover {
  background-color: #DC2626;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-4);
}

.detail-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  color: var(--color-gray-600);
  font-size: 0.875rem;
}

.icon-blue { color: var(--color-blue); }
.icon-green { color: #16A34A; }
.icon-red { color: #DC2626; }
.icon-yellow { color: #CA8A04; }

.price {
  color: #16A34A;
  font-weight: 600;
}

.price-detail {
  font-size: 0.75rem;
  color: var(--color-gray-500);
}

.message-box {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-2);
  padding: var(--spacing-4);
  background: var(--color-gray-50);
  border-radius: var(--radius-lg);
}

.message-box svg {
  color: var(--color-gray-400);
  margin-top: 0.25rem;
  flex-shrink: 0;
}

.message-label {
  font-size: 0.875rem;
  color: var(--color-gray-600);
  margin: 0 0 var(--spacing-1) 0;
}

.message-text {
  color: var(--color-gray-800);
  margin: 0;
}

.empty-state {
  text-align: center;
  padding: var(--spacing-12) var(--spacing-6);
  color: var(--color-gray-500);
}

.loading-state {
  text-align: center;
  padding: var(--spacing-12) var(--spacing-6);
  color: var(--color-gray-600);
}

.materials-section {
  padding: var(--spacing-4);
  background: var(--color-gray-50);
  border-radius: var(--radius-lg);
  margin-top: var(--spacing-4);
}

.materials-header {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  margin-bottom: var(--spacing-3);
}

.materials-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-gray-700);
  margin: 0;
}

.materials-group {
  margin-bottom: var(--spacing-3);
}

.materials-subtitle {
  font-size: 0.75rem;
  color: var(--color-gray-600);
  margin: 0 0 var(--spacing-2) 0;
}

.materials-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-2);
}

.material-tag {
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.client-tag {
  background-color: #D1FAE5;
  color: #065F46;
}

.intervenant-tag {
  background-color: #DBEAFE;
  color: #1E40AF;
}

.error-message {
  padding: var(--spacing-4);
  background-color: #FEE2E2;
  color: #DC2626;
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-4);
  border: 1px solid #FCA5A5;
}

.error-message {
  padding: var(--spacing-4);
  background-color: #FEE2E2;
  color: #DC2626;
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-4);
  border: 1px solid #FCA5A5;
}

.rating-btn-public {
  background-color: #4F46E5;
}

.rating-btn-public:hover {
  background-color: #4338CA;
}

.expired-notice {
  font-size: 0.75rem;
  color: #9CA3AF;
  font-style: italic;
}

/* Mail Notification */
.mail-notification {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 99999;
  background: white;
  border-radius: 1rem;
  padding: 1rem 1.5rem;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  border: 1px solid #E5E7EB;
  border-left: 4px solid #E8793F;
  max-width: 400px;
}

.notif-icon {
  background: #FFF7ED;
  color: #E8793F;
  padding: 0.5rem;
  border-radius: 0.75rem;
  display: flex;
}

.notif-msg {
  font-size: 0.875rem;
  color: #374151;
  font-weight: 500;
  margin: 0;
  line-height: 1.4;
}

.notif-close {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  color: #9CA3AF;
  cursor: pointer;
  padding: 0 0.5rem;
  margin-left: 0.5rem;
}

.notif-close:hover {
  color: #374151;
}

/* Animations */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Modal Overlay - Shared styling */
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
  padding: var(--spacing-4);
}

/* Public Evaluations Modal */
.public-modal-content {
  background: #FEF9E6;
  border-radius: var(--radius-xl);
  max-width: 65rem;
  width: 95%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  border: 3px solid #FEE347;
}

/* Complaint Modal */
.complaint-modal-content {
  background: white;
  border-radius: var(--radius-xl);
  max-width: 32rem;
  width: 95%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.complaint-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-6);
  border-bottom: 1px solid #E5E7EB;
}

.complaint-modal-title {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  margin: 0;
  color: #EF4444;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6B7280;
  cursor: pointer;
  padding: var(--spacing-1);
  line-height: 1;
}

.modal-close-btn:hover {
  color: #374151;
}

.complaint-modal-body {
  padding: var(--spacing-6);
}

.intervention-info {
  background-color: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: var(--radius-md);
  padding: var(--spacing-4);
  margin-bottom: var(--spacing-6);
}

.info-title {
  margin: 0 0 var(--spacing-3) 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-3);
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-1);
}

.info-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6B7280;
}

.info-value {
  font-size: 0.875rem;
  font-weight: 500;
  color: #111827;
}

.complaint-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-2);
}

.form-label {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.form-input,
.form-select,
.form-textarea {
  padding: var(--spacing-3);
  border: 1px solid #D1D5DB;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #EF4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.complaint-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--spacing-3);
  padding: var(--spacing-6);
  border-top: 1px solid #E5E7EB;
  background-color: #F9FAFB;
}

.btn-cancel,
.btn-submit {
  padding: var(--spacing-3) var(--spacing-4);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.btn-cancel {
  background-color: white;
  color: #6B7280;
  border-color: #D1D5DB;
}

.btn-cancel:hover:not(:disabled) {
  background-color: #F3F4F6;
  color: #374151;
}

.btn-submit {
  background-color: #EF4444;
  color: white;
}

.btn-submit:hover:not(:disabled) {
  background-color: #DC2626;
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.public-modal-body {
  padding: var(--spacing-8);
}

.public-modal-title {
  margin: 0 0 var(--spacing-6) 0;
  color: #E8793F;
  font-size: 1.75rem;
  font-weight: 600;
  text-align: center;
}

.evaluations-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-6);
  margin-bottom: var(--spacing-6);
}

.evaluation-section {
  background: rgba(255, 255, 255, 0.6);
  border-radius: var(--radius-lg);
  padding: var(--spacing-5);
  border: 2px solid rgba(255, 159, 64, 0.3);
}

.evaluation-section h3 {
  margin: 0 0 var(--spacing-4) 0;
  color: #E8793F;
  font-size: 1.125rem;
  font-weight: 600;
  padding-bottom: var(--spacing-2);
  border-bottom: 2px solid #FEE347;
}

.rating-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-3) 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.rating-item:last-of-type {
  border-bottom: none;
}

.criteria-name {
  font-weight: 500;
  color: var(--color-gray-700);
  font-size: 0.9375rem;
}

.stars {
  display: flex;
  gap: 0.25rem;
}

.star {
  color: #D1D5DB;
}

.star-filled {
  color: #FEE347;
}

.comment-box {
  margin-top: var(--spacing-4);
  padding: var(--spacing-3);
  background: white;
  border-radius: var(--radius-md);
  border-left: 3px solid #E8793F;
}

.comment-box strong {
  display: block;
  margin-bottom: var(--spacing-2);
  color: var(--color-gray-700);
  font-size: 0.875rem;
  font-weight: 600;
}

.comment-box p {
  margin: 0;
  color: var(--color-gray-600);
  font-size: 0.9375rem;
  line-height: 1.6;
}

.no-comment {
  font-style: italic;
  color: #9CA3AF !important;
}

.no-evaluation {
  text-align: center;
  color: var(--color-gray-500);
  font-style: italic;
  padding: var(--spacing-8) var(--spacing-4);
}

.visibility-info {
  text-align: center;
  padding: var(--spacing-4);
  background: rgba(219, 234, 254, 0.5);
  border-radius: var(--radius-lg);
  border: 1px solid #93C5FD;
  margin-bottom: var(--spacing-4);
}

.visibility-info p {
  margin: 0;
  color: #1E40AF;
  font-weight: 500;
  font-size: 0.9375rem;
}

.public-modal-actions {
  display: flex;
  justify-content: center;
}

.close-modal-btn {
  padding: var(--spacing-3) var(--spacing-8);
  border: 1px solid #D1D5DB;
  background: white;
  color: var(--color-gray-700);
  border-radius: var(--radius-lg);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9375rem;
}

.close-modal-btn:hover {
  background: var(--color-gray-50);
  border-color: #9CA3AF;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }

  .reservation-content {
    flex-direction: column;
  }

  .action-buttons {
    flex-direction: column;
    width: 100%;
  }

  .tabs {
    flex-direction: column;
  }
  
  .evaluations-grid {
    grid-template-columns: 1fr;
  }
  
  .public-modal-content {
    width: 98%;
  }
}
/* New Modal Styles */
.confirmation-modal-content {
  background: white;
  padding: 0;
  border-radius: var(--radius-xl);
  max-width: 450px;
  width: 90%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.modal-header.warning-header {
  background-color: #FEF2F2;
  border-bottom: 1px solid #FEE2E2;
  padding: var(--spacing-6);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-3);
  text-align: center;
}

.warning-icon {
  color: #DC2626;
  background: #FEE2E2;
  padding: 8px;
  border-radius: 50%;
  width: 48px;
  height: 48px;
}

.modal-header h2 {
  color: #991B1B;
  font-size: 1.25rem;
  margin: 0;
}

.modal-body-text {
  padding: var(--spacing-6);
  text-align: center;
  color: var(--color-gray-600);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--spacing-3);
  padding: var(--spacing-4) var(--spacing-6);
  background-color: var(--color-gray-50);
  border-top: 1px solid var(--color-gray-100);
}

.btn-cancel {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid var(--color-gray-300);
  border-radius: var(--radius-lg);
  color: var(--color-gray-700);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: var(--color-gray-50);
}

.btn-confirm-danger {
  padding: 0.5rem 1rem;
  background: #DC2626;
  color: white;
  border: none;
  border-radius: var(--radius-lg);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-confirm-danger:hover {
  background: #B91C1C;
}
.invoice-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #E5E7EB;
  color: #4B5563;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.invoice-btn:hover {
  background: #F9FAFB;
  border-color: #D1D5DB;
  color: #111827;
}

.accepted-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}
</style>
