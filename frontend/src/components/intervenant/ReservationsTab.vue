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
      <div class="reservations-list">
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
                <span v-else-if="selectedTab === 'accepted'" class="status-badge status-accepted">
                  Confirmée
                </span>
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
      </div>

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
              ✅ Les deux parties ont évalué
            </p>
            <p v-else-if="selectedReservation.publicData.window_passed">
              ⏰ Délai de 7 jours expiré
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Check, X, Clock, MapPin, Calendar, MessageSquare, Coins, Package, Star } from 'lucide-vue-next'
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
const showPublicModal = ref(false)
const showClientProfile = ref(false)
const selectedClientId = ref(null)
const selectedService = ref('all')
const allServices = ref([])
const showDetailsModal = ref(false)
const selectedInterventionId = ref(null)

const fetchAllServices = async () => {
  try {
    const response = await api.get('services')
    allServices.value = response.data.services || []
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

const fetchReservations = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await reservationService.getMyReservations()
    const fetchedReservations = response.data.reservations || []
    
    // Check evaluation status for completed reservations
    for (const reservation of fetchedReservations) {
      if (reservation.status === 'completed') {
        try {
          // Get detailed evaluation status
          const statusData = await evaluationService.canRateClient(reservation.id)
          console.log('Evaluation status for reservation', reservation.id, statusData)
          
          reservation.evaluationStatus = statusData.status
          
          // Show public evaluations button if:
          // 1. Both parties have rated, OR
          // 2. 7-day window has passed (we need to try calling getPublicEvaluations to know)
          // For now, just check if both_parties_rated is true from backend
          reservation.canViewPublic = statusData.both_parties_rated === true
          
          // Also try to check if window passed by attempting to fetch public evaluations
          if (!reservation.canViewPublic && statusData.status === 'view_only') {
            try {
              const publicData = await evaluationService.getPublicEvaluations(reservation.id)
              if (publicData.can_view) {
                reservation.canViewPublic = true
              }
            } catch (err) {
              // If 403, evaluations not public yet
              reservation.canViewPublic = false
            }
          }
        } catch (err) {
          console.error('Error checking evaluation status:', err)
          reservation.evaluationStatus = 'unknown'
          reservation.canViewPublic = false
        }
      }
    }
    
    reservations.value = fetchedReservations
  } catch (err) {
    error.value = err.message || 'Erreur lors du chargement des réservations'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const acceptReservation = async (id) => {
  try {
    await reservationService.acceptReservation(id)
    // Refresh the data to get updated status
    await fetchReservations()
  } catch (err) {
    alert(err.message || 'Erreur lors de l\'acceptation de la réservation')
    console.error(err)
  }
}

const refuseReservation = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir refuser cette réservation ?')) {
    try {
      await reservationService.refuseReservation(id)
      // Refresh the data to get updated status
      await fetchReservations()
    } catch (err) {
      alert(err.message || 'Erreur lors du refus de la réservation')
      console.error(err)
    }
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

onMounted(async () => {
  await Promise.all([
    fetchReservations(),
    fetchAllServices()
  ])
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
</style>
