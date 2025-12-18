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

      <!-- Reservations List -->
      <div class="reservations-list">
        <div
          v-for="reservation in filteredReservations"
          :key="reservation.id"
          class="reservation-card"
        >
          <div class="reservation-content">
            <img :src="reservation.clientImage" :alt="reservation.clientName" class="client-avatar" />

            <div class="reservation-details">
              <div class="reservation-header">
                <div>
                  <h3>{{ reservation.clientName }}</h3>
                  <p class="task-name">{{ reservation.task }}</p>
                </div>

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
                  <button @click="openRatingModal(reservation)" class="rating-btn">
                    <Star :size="16" />
                    Évaluer
                  </button>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Check, X, Clock, MapPin, Calendar, MessageSquare, Coins, Package, Star } from 'lucide-vue-next'
import reservationService from '@/services/intervenantReservationService'
import evaluationService from '@/services/evaluationService'
import api from '@/services/api'
import ClientRatingModal from './ClientRatingModal.vue'

const selectedTab = ref('pending')
const reservations = ref([])
const loading = ref(false)
const error = ref(null)
const showRatingModal = ref(false)
const selectedReservation = ref(null)

const filteredReservations = computed(() => {
  return reservations.value.filter(r => r.status === selectedTab.value)
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
    reservations.value = response.data.reservations || []
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
    
    if (data.can_rate) {
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

const onRatingSubmitted = async () => {
  await fetchReservations()
}

onMounted(() => {
  fetchReservations()
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
}
</style>
