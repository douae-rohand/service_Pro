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

                <!-- Status Badges -->
                <span v-else-if="selectedTab === 'accepted'" class="status-badge status-accepted">
                  Confirmée
                </span>
                <span v-else-if="selectedTab === 'completed'" class="status-badge status-completed">
                  Terminée
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
            </div>
          </div>
        </div>
      </div>

      <div v-if="filteredReservations.length === 0" class="empty-state">
        <p>Aucune réservation {{ selectedTab === 'pending' ? 'en attente' : selectedTab === 'accepted' ? 'acceptée' : 'complétée' }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Check, X, Clock, MapPin, Calendar, MessageSquare, Coins } from 'lucide-vue-next'
import reservationService from '@/services/intervenantReservationService'

const selectedTab = ref('pending')
const reservations = ref([])
const loading = ref(false)
const error = ref(null)

const filteredReservations = computed(() => {
  return reservations.value.filter(r => r.status === selectedTab.value)
})

const pendingCount = computed(() => reservations.value.filter(r => r.status === 'pending').length)
const acceptedCount = computed(() => reservations.value.filter(r => r.status === 'accepted').length)
const completedCount = computed(() => reservations.value.filter(r => r.status === 'completed').length)

const tabs = computed(() => [
  { id: 'pending', label: 'En Attente', color: '#E8793F', count: pendingCount.value },
  { id: 'accepted', label: 'Acceptées', color: '#92B08B', count: acceptedCount.value },
  { id: 'completed', label: 'Complétées', color: '#1A5FA3', count: completedCount.value }
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
    reservations.value = response.data
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
    const reservation = reservations.value.find(r => r.id === id)
    if (reservation) {
      reservation.status = 'accepted'
    }
  } catch (err) {
    alert(err.message || 'Erreur lors de l\'acceptation de la réservation')
    console.error(err)
  }
}

const refuseReservation = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir refuser cette réservation ?')) {
    try {
      await reservationService.refuseReservation(id)
      const reservation = reservations.value.find(r => r.id === id)
      if (reservation) {
        reservation.status = 'refused'
      }
    } catch (err) {
      alert(err.message || 'Erreur lors du refus de la réservation')
      console.error(err)
    }
  }
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
