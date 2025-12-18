<template>
  <div v-if="show" class="modal-overlay" @click="close">
    <div class="details-modal" @click.stop>
      <div v-if="loading" class="loading-state">
        <div class="loader"></div>
        <p>Chargement des détails...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="close" class="btn-primary">Fermer</button>
      </div>

      <div v-else-if="intervention" class="details-content">
        <!-- Header -->
        <div class="modal-header">
          <div class="header-main">
            <h2>Détails de l'intervention</h2>
            <span class="status-badge" :class="'status-' + intervention.status.replace(' ', '-')">
              {{ formatStatus(intervention.status) }}
            </span>
          </div>
          <button @click="close" class="close-icon-btn">
            <X :size="24" />
          </button>
        </div>

        <div class="details-grid">
          <!-- Left Column: Info -->
          <div class="info-section">
            <!-- Client Info Block -->
            <div class="info-block">
              <div class="block-header">
                <User :size="18" />
                <h3>Client</h3>
              </div>
              <div class="client-mini-card">
                <img :src="intervention.client?.utilisateur?.photo || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop'" class="mini-avatar" />
                <div class="mini-info">
                  <p class="name">{{ intervention.client?.utilisateur?.nom }} {{ intervention.client?.utilisateur?.prenom }}</p>
                  <p class="contact">{{ intervention.client?.utilisateur?.email }}</p>
                </div>
              </div>
            </div>

            <!-- Service Details Block -->
            <div class="info-block">
              <div class="block-header">
                <Package :size="18" />
                <h3>Service & Tâche</h3>
              </div>
              <div class="service-details">
                <p class="service-name">{{ intervention.tache?.service?.nom_service }}</p>
                <p class="task-name">{{ intervention.tache?.nom_tache }}</p>
                <p class="description" v-if="intervention.tache?.description">{{ intervention.tache.description }}</p>
              </div>
            </div>

            <!-- Address Block -->
            <div class="info-block">
              <div class="block-header">
                <MapPin :size="18" />
                <h3>Lieu</h3>
              </div>
              <p class="address-text">{{ intervention.address }}, {{ intervention.ville }}</p>
            </div>

            <!-- Timing Block -->
            <div class="info-block">
              <div class="block-header">
                <Clock :size="18" />
                <h3>Planification</h3>
              </div>
              <div class="timing-grid">
                <div class="timing-item">
                  <span class="t-label">Date</span>
                  <span class="t-value">{{ formatDate(intervention.date_intervention) }}</span>
                </div>
                <div class="timing-item">
                  <span class="t-label">Heure</span>
                  <span class="t-value">{{ formatTime(intervention.date_intervention) }}</span>
                </div>
                <div class="timing-item">
                  <span class="t-label">Durée</span>
                  <span class="t-value">2 heures (estimé)</span>
                </div>
              </div>
            </div>

            <!-- Complementary Info Block -->
            <div v-if="intervention.informations && intervention.informations.length > 0" class="info-block">
              <div class="block-header">
                <ClipboardList :size="18" />
                <h3>Informations Complémentaires</h3>
              </div>
              <div class="complementary-info">
                <div v-for="info in intervention.informations" :key="info.id" class="info-row">
                  <span class="info-label">{{ info.nom }} :</span>
                  <span class="info-value">{{ info.pivot?.information || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Materials & Photos -->
          <div class="media-section">
            <!-- Materials Block -->
            <div class="info-block">
              <div class="block-header">
                <Tool :size="18" />
                <h3>Matériels</h3>
              </div>
              <div v-if="intervention.materiels && intervention.materiels.length > 0" class="materials-list">
                <div v-for="mat in intervention.materiels" :key="mat.id" class="material-tag">
                  {{ mat.nom_materiel }}
                </div>
              </div>
              <p v-else class="empty-hint">Aucun matériel spécifique renseigné</p>
            </div>

            <!-- Photo Gallery Block -->
            <div v-if="intervention.photos && intervention.photos.length > 0" class="info-block">
              <div class="block-header">
                <Camera :size="18" />
                <h3>Photos de la demande</h3>
              </div>
              
              <!-- Avant Group -->
              <div v-if="groupedPhotos.avant.length > 0" class="photo-group">
                <p class="photo-phase-label">Avant Intervention</p>
                <div class="photo-gallery">
                  <div v-for="photo in groupedPhotos.avant" :key="photo.id" class="photo-item">
                    <img :src="getImageUrl(photo.photo_path)" :alt="photo.description" class="gallery-img" @click="openImage(photo)" />
                  </div>
                </div>
              </div>

              <!-- Après Group -->
              <div v-if="groupedPhotos.apres.length > 0" class="photo-group" style="margin-top: 1.5rem;">
                <p class="photo-phase-label">Après Intervention</p>
                <div class="photo-gallery">
                  <div v-for="photo in groupedPhotos.apres" :key="photo.id" class="photo-item">
                    <img :src="getImageUrl(photo.photo_path)" :alt="photo.description" class="gallery-img" @click="openImage(photo)" />
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="info-block">
              <div class="block-header">
                <Camera :size="18" />
                <h3>Photos de la demande</h3>
              </div>
              <p class="empty-hint">Aucune photo partagée pour le moment</p>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="modal-footer">
          <button @click="close" class="btn-secondary">Fermer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { X, User, MapPin, Clock, Package, Camera, PenTool as Tool, ClipboardList } from 'lucide-vue-next'
import api from '@/services/api'

const props = defineProps({
  show: Boolean,
  interventionId: Number
})

const emit = defineEmits(['close'])

const intervention = ref(null)
const loading = ref(false)
const error = ref(null)

const groupedPhotos = computed(() => {
  const groups = { avant: [], apres: [] }
  if (!intervention.value?.photos) return groups
  
  intervention.value.photos.forEach(photo => {
    if (photo.phase_prise === 'apres') {
      groups.apres.push(photo)
    } else {
      groups.avant.push(photo)
    }
  })
  return groups
})

watch(() => props.show, (newVal) => {
  if (newVal && props.interventionId) {
    fetchDetails()
  }
})

const fetchDetails = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get(`interventions/${props.interventionId}`)
    intervention.value = response.data
  } catch (err) {
    console.error('Error fetching intervention details:', err)
    error.value = "Impossible de charger les détails de l'intervention."
  } finally {
    loading.value = false
  }
}

const close = () => {
  emit('close')
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatTime = (dateStr) => {
  return new Date(dateStr).toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatStatus = (status) => {
  const map = {
    'en attend': 'En attente',
    'acceptee': 'Acceptée',
    'refuse': 'Refusée',
    'termine': 'Terminée'
  }
  return map[status] || status
}

const openImage = (path) => {
  window.open('/storage/' + path, '_blank')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 1.5rem;
  backdrop-filter: blur(4px);
}

.details-modal {
  background: #FFFFFF;
  width: 100%;
  max-width: 60rem;
  max-height: 90vh;
  border-radius: 1.25rem;
  overflow-y: auto;
  border: 1px solid #E5E7EB;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  position: relative;
}

.loading-state, .error-state {
  padding: 4rem;
  text-align: center;
}

.details-content {
  padding: 2rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #F3F4F6;
}

.header-main h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}

.status-badge {
  padding: 0.375rem 0.875rem;
  border-radius: 9999px;
  font-size: 0.8125rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
}

.status-en-attend { background: #FFF7ED; color: #EA580C; border: 1px solid #FFEDD5; }
.status-acceptee { background: #F0FDF4; color: #16A34A; border: 1px solid #DCFCE7; }
.status-termine { background: #EFF6FF; color: #2563EB; border: 1px solid #DBEAFE; }

.close-icon-btn {
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  color: #6B7280;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.5rem;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-icon-btn:hover {
  background: #F3F4F6;
  color: #111827;
}

.details-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 2.5rem;
}

.info-block {
  margin-bottom: 2rem;
}

.block-header {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  margin-bottom: 1rem;
}

.block-header :deep(svg) {
  color: #E8793F;
}

.block-header h3 {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4B5563;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.client-mini-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: #F9FAFB;
  border-radius: 0.75rem;
  border: 1px solid #F3F4F6;
}

.mini-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid white;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.name { font-weight: 600; color: #111827; font-size: 1.0625rem; }
.contact { font-size: 0.875rem; color: #6B7280; }

.service-details {
  padding: 1.25rem;
  background: white;
  border-radius: 0.75rem;
  border: 1px solid #E5E7EB;
}

.service-name { font-weight: 700; color: #111827; margin-bottom: 0.25rem; }
.task-name { font-weight: 500; color: #E8793F; }
.description { margin-top: 0.75rem; font-size: 0.875rem; color: #6B7280; line-height: 1.5; }

.address-text {
  font-size: 1rem;
  color: #374151;
  padding: 0.5rem 1rem;
  background: #F9FAFB;
  border-radius: 0.5rem;
  border: 1px solid #F3F4F6;
  display: inline-block;
}

.timing-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.timing-item {
  display: flex;
  flex-direction: column;
  padding: 1rem;
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 0.75rem;
}

.t-label { font-size: 0.75rem; text-transform: uppercase; color: #9CA3AF; font-weight: 700; margin-bottom: 0.25rem; }
.t-value { font-weight: 600; color: #111827; font-size: 0.875rem; }

.complementary-info {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 0.75rem;
  overflow: hidden;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.875rem 1.25rem;
  border-bottom: 1px solid #F3F4F6;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-size: 0.875rem;
  color: #6B7280;
  font-weight: 500;
}

.info-value {
  font-size: 0.875rem;
  color: #111827;
  font-weight: 600;
  text-align: right;
}

.materials-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.625rem;
}

.material-tag {
  padding: 0.5rem 0.875rem;
  background: #F3F4F6;
  border: 1px solid #E5E7EB;
  border-radius: 0.625rem;
  font-size: 0.8125rem;
  color: #374151;
  font-weight: 500;
}

.photo-phase-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #9CA3AF;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  margin-bottom: 0.75rem;
}

.photo-gallery {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.photo-item {
  aspect-ratio: 16/10;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.gallery-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  cursor: pointer;
  transition: opacity 0.2s;
}

.gallery-img:hover { opacity: 0.9; }

.empty-hint { font-size: 0.875rem; color: #9CA3AF; font-style: italic; }

.modal-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 2.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #F3F4F6;
}

.btn-secondary {
  padding: 0.625rem 1.5rem;
  border-radius: 0.625rem;
  border: 1px solid #D1D5DB;
  background: white;
  color: #374151;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #F9FAFB;
  border-color: #9CA3AF;
}

@media (max-width: 992px) {
  .details-grid { grid-template-columns: 1fr; gap: 2rem; }
  .photo-gallery { grid-template-columns: repeat(3, 1fr); }
}
</style>
