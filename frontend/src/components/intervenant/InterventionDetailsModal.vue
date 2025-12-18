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
            <span class="status-badge" :class="'status-' + (intervention.status || '').replace(' ', '-')">
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
                <img :src="getImageUrl(intervention.client?.utilisateur?.photo)" class="mini-avatar" />
                <div class="mini-info">
                  <p class="name">{{ intervention.client?.utilisateur?.nom }} {{ intervention.client?.utilisateur?.prenom }}</p>
                  
                  <!-- Privacy: Contact info hidden in modal, sent via email instead -->
                  <div class="privacy-notice">
                    <span class="lock-icon"><Lock :size="12" /></span>
                    Coordonnées envoyées par email lors de l'acceptation
                  </div>
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
                <p class="service-name">{{ intervention.tache?.service?.nom_service || 'Service non spécifié' }}</p>
                <p class="task-name">{{ intervention.tache?.nom_tache || 'Tâche non spécifiée' }}</p>
                <p class="description" v-if="intervention.tache?.description">{{ intervention.tache.description }}</p>
              </div>
            </div>

            <!-- Address Block -->
            <div class="info-block">
              <div class="block-header">
                <MapPin :size="18" />
                <h3>Lieu</h3>
              </div>
              <div class="address-privacy">
                <p class="city-only">{{ intervention.ville }}</p>
                <div class="privacy-notice small mt-1">
                  <span class="lock-icon"><Lock :size="12" /></span>
                  Adresse exacte envoyée par email
                </div>
              </div>
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
              
              <!-- Client Provided -->
              <div v-if="intervention.tache?.materiels?.length > 0" class="materials-group">
                <p class="mat-label-small">Fournis par le client :</p>
                <div class="materials-list">
                  <div v-for="mat in intervention.tache.materiels" :key="'c'+mat.id" class="material-tag client-mat">
                    {{ mat.nom_materiel }}
                  </div>
                </div>
              </div>

              <!-- Intervenant Provided -->
              <div v-if="intervention.materiels?.length > 0" class="materials-group" :class="{ 'mt-3': intervention.tache?.materiels?.length > 0 }">
                <p class="mat-label-small">Vos matériels :</p>
                <div class="materials-list">
                  <div v-for="mat in intervention.materiels" :key="'i'+mat.id" class="material-tag my-mat">
                    {{ mat.nom_materiel }}
                  </div>
                </div>
              </div>

              <p v-if="!intervention.materiels?.length && !intervention.tache?.materiels?.length" class="empty-hint">Aucun matériel spécifique renseigné</p>
            </div>

            <!-- Photo Gallery Block -->
            <div class="info-block">
              <div class="block-header">
                <Camera :size="18" />
                <h3>Photos de l'intervention</h3>
              </div>
              
              <!-- Action: Ajouter des photos (Uniquement après l'intervention) -->
              <div v-if="intervention.status === 'termine'" class="add-photos-action mb-4">
                <label class="add-photos-btn">
                  <Plus :size="16" />
                  Ajouter des photos (Après travaux)
                  <input type="file" multiple accept="image/*" @change="handleFileUpload" class="hidden-input" :disabled="uploading" />
                </label>
                <p v-if="uploading" class="upload-status">Téléchargement en cours...</p>
              </div>

              <div v-if="intervention.photos && intervention.photos.length > 0">
                <!-- Avant Group -->
                <div v-if="groupedPhotos.avant.length > 0" class="photo-group">
                  <p class="photo-phase-label">Avant Intervention</p>
                  <div class="photo-gallery">
                    <div v-for="photo in groupedPhotos.avant" :key="photo.id" class="photo-item">
                      <img :src="getImageUrl(photo.photo_path)" :alt="photo.description" class="gallery-img" @click="openImage(photo.photo_path)" />
                    </div>
                  </div>
                </div>

                <!-- Après Group -->
                <div v-if="groupedPhotos.apres.length > 0" class="photo-group" style="margin-top: 1.5rem;">
                  <p class="photo-phase-label">Après Intervention</p>
                  <div class="photo-gallery">
                    <div v-for="photo in groupedPhotos.apres" :key="photo.id" class="photo-item">
                      <img :src="getImageUrl(photo.photo_path)" :alt="photo.description" class="gallery-img" @click="openImage(photo.photo_path)" />
                    </div>
                  </div>
                </div>
              </div>
              <p v-else class="empty-hint">Aucune photo pour le moment</p>
            </div>
          </div>
        </div>

        <!-- Evaluations & Feedbacks (Full Width Section) -->
        <div v-if="intervention.status === 'termine' && (intervention.evaluations?.length > 0 || intervention.commentaires?.length > 0)" class="evaluation-full-section">
          <div class="section-title">
            <ClipboardList :size="20" />
            <h3>Évaluation & Feedback</h3>
          </div>
          
          <div class="evaluation-content-grid">
            <!-- Client Ratings & Comment -->
            <div class="eval-column client-feedback">
              <div class="side-header">
                <span class="side-tag client">Côté Client</span>
                <p class="sub-label">Évaluation de l'intervenant</p>
              </div>

              <div v-if="clientEvaluations.length > 0" class="details-ratings-list mb-4">
                <div v-for="evalItem in clientEvaluations" :key="evalItem.id" class="eval-row-inline">
                  <span class="eval-crit-name">{{ evalItem.critaire?.nom_critaire }}</span>
                  <div class="eval-stars-inline">
                    <Star 
                      v-for="n in 5" 
                      :key="n" 
                      :size="14" 
                      :fill="n <= evalItem.note ? '#2563EB' : 'none'"
                      class="star-icon"
                      :class="{ 'filled-client': n <= evalItem.note }"
                    />
                    <span class="note-digit">{{ evalItem.note }}/5</span>
                  </div>
                </div>
              </div>

              <div v-if="clientComment" class="detail-comment-card client">
                <p class="comment-msg-text">"{{ clientComment.commentaire }}"</p>
              </div>

              <!-- Privacy Notice for Hidden Client Rating -->
              <div v-else-if="intervention.ratings_meta?.client_has_rated && !intervention.ratings_meta?.is_public" class="privacy-explanation">
                <div class="lock-circle">
                  <Lock :size="16" />
                </div>
                <p>Le client a laissé une évaluation, mais elle reste privée jusqu'à ce que vous donniez la vôtre (ou après 7 jours).</p>
              </div>
              
              <p v-else-if="clientEvaluations.length === 0" class="empty-hint">Aucune évaluation client</p>
            </div>

            <!-- Intervenant Ratings & Comment -->
            <div class="eval-column intervenant-feedback">
              <div class="side-header">
                <span class="side-tag intervenant">Votre Côté</span>
                <p class="sub-label">Votre évaluation du client</p>
              </div>

              <div v-if="intervenantEvaluations.length > 0" class="details-ratings-list mb-4">
                <div v-for="evalItem in intervenantEvaluations" :key="evalItem.id" class="eval-row-inline">
                  <span class="eval-crit-name">{{ evalItem.critaire?.nom_critaire }}</span>
                  <div class="eval-stars-inline">
                    <Star 
                      v-for="n in 5" 
                      :key="n" 
                      :size="14" 
                      :fill="n <= evalItem.note ? '#E8793F' : 'none'"
                      class="star-icon"
                      :class="{ 'filled-intervenant': n <= evalItem.note }"
                    />
                    <span class="note-digit">{{ evalItem.note }}/5</span>
                  </div>
                </div>
              </div>

              <div v-if="intervenantComment" class="detail-comment-card intervenant">
                <p class="comment-msg-text">"{{ intervenantComment.commentaire }}"</p>
              </div>

              <!-- Privacy Notice for Hidden Intervenant Rating -->
              <div v-else-if="intervention.ratings_meta?.intervenant_has_rated && !intervention.ratings_meta?.is_public" class="privacy-explanation">
                <div class="lock-circle">
                  <Lock :size="16" />
                </div>
                <p>Votre évaluation est enregistrée. Elle sera visible publiquement une fois que le client aura évalué (ou après 7 jours).</p>
              </div>

              <p v-else-if="intervenantEvaluations.length === 0" class="empty-hint">Vous n'avez pas encore évalué ce client</p>
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
import { X, User, MapPin, Clock, Package, Camera, PenTool as Tool, ClipboardList, Mail, Phone, Star, Plus, Lock } from 'lucide-vue-next'
import api from '@/services/api'

const props = defineProps({
  show: Boolean,
  interventionId: Number
})

const emit = defineEmits(['close'])

const intervention = ref(null)
const loading = ref(false)
const uploading = ref(false)
const error = ref(null)

const clientEvaluations = computed(() => {
  return intervention.value?.evaluations?.filter(e => e.type_auteur === 'client') || []
})

const intervenantEvaluations = computed(() => {
  return intervention.value?.evaluations?.filter(e => e.type_auteur === 'intervenant') || []
})

const clientComment = computed(() => {
  return intervention.value?.commentaires?.find(c => c.type_auteur === 'client')
})

const intervenantComment = computed(() => {
  return intervention.value?.commentaires?.find(c => c.type_auteur === 'intervenant')
})

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
    const response = await api.get(`intervenant-interventions/${props.interventionId}`)
    // Laravel Resource wraps data in a 'data' property
    intervention.value = response.data.data || response.data
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
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatTime = (dateStr) => {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatStatus = (status) => {
  if (!status) return 'N/A'
  const map = {
    'en attend': 'En attente',
    'en_attente': 'En attente',
    'pending': 'En attente',
    'acceptee': 'Acceptée',
    'accepted': 'Acceptée',
    'refuse': 'Refusée',
    'rejected': 'Refusée',
    'termine': 'Terminée',
    'completed': 'Terminée'
  }
  return map[status] || status
}



const getImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400&h=300&fit=crop'
  if (path.startsWith('http')) return path
  return `http://127.0.0.1:8000/storage/${path.replace(/^\/+/, '')}`
}

const openImage = (photo) => {
  const path = photo.photo_path || photo.path || photo
  if (!path) return
  window.open(getImageUrl(path), '_blank')
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
.contact { font-size: 0.875rem; color: #6B7280; display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem; }

.privacy-notice {
  font-size: 0.75rem;
  color: #9CA3AF;
  font-style: italic;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: 0.4rem;
}

.lock-icon {
  color: #E8793F;
}

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

/* Privacy Explanation */
.privacy-explanation {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 1.5rem;
  background: #F8FAFC;
  border-radius: 1rem;
  border: 1px dashed #CBD5E1;
  gap: 0.75rem;
}

.privacy-explanation p {
  font-size: 0.8125rem;
  color: #64748B;
  line-height: 1.5;
  margin: 0;
}

.lock-circle {
  width: 36px;
  height: 36px;
  background: #EFF6FF;
  color: #2563EB;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
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

.client-mat {
  background: #EFF6FF;
  border-color: #DBEAFE;
  color: #1E40AF;
}

.my-mat {
  background: #FFF7ED;
  border-color: #FFEDD5;
  color: #9A3412;
}

.mat-label-small {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #9CA3AF;
  margin-bottom: 0.5rem;
}

.mt-3 { margin-top: 1rem; }

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

/* Evaluation Section Styles */
.evaluation-full-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 2px dashed #F3F4F6;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  color: #111827;
}

.section-title h3 {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
}

.evaluation-content-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.sub-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #9CA3AF;
  margin-bottom: 1rem;
  letter-spacing: 0.05em;
}

.details-ratings-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.eval-row-inline {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #F9FAFB;
  border-radius: 0.75rem;
}

.eval-crit-name {
  font-size: 0.875rem;
  color: #4B5563;
  font-weight: 500;
}

.eval-stars-inline {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.star-icon {
  color: #E2E8F0;
}

.star-icon.filled-client {
  color: #2563EB;
}

.star-icon.filled-intervenant {
  color: #E8793F;
}

.note-digit {
  font-size: 0.875rem;
  font-weight: 700;
  color: #111827;
  margin-left: 0.5rem;
}

.details-comments-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-comment-card {
  padding: 1.25rem;
  border-radius: 1rem;
  background: white;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.detail-comment-card.intervenant {
  border-left: 4px solid #E8793F;
  background: #FFFBF9;
}

.detail-comment-card.client {
  border-left: 4px solid #2563EB;
  background: #F9FBFF;
}

.side-header {
  margin-bottom: 1.25rem;
}

.side-tag {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}

.side-tag.client {
  background: #DBEAFE;
  color: #1E40AF;
}

.side-tag.intervenant {
  background: #FFEDD5;
  color: #9A3412;
}

.author-tag {
  font-size: 0.75rem;
  font-weight: 700;
  color: #6B7280;
  display: block;
  margin-bottom: 0.5rem;
}

.comment-msg-text {
  font-size: 0.9375rem;
  color: #374151;
  line-height: 1.5;
  font-style: italic;
  margin: 0;
}

/* Photo Upload Actions */
.add-photos-action {
  margin-bottom: 1.5rem;
}

.add-photos-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  border: 1px dashed #E8793F;
  color: #E8793F;
  border-radius: 0.625rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.add-photos-btn:hover {
  background: #FFF7ED;
  border-style: solid;
}

.hidden-input {
  display: none;
}

.upload-status {
  font-size: 0.75rem;
  color: #6B7280;
  margin-top: 0.5rem;
}

.mb-4 { margin-bottom: 1rem; }
.mt-1 { margin-top: 0.25rem; }

.address-text, .city-only { 
  font-size: 1rem; 
  color: #374151; 
  font-weight: 500; 
  margin: 0;
}

.privacy-notice.small { 
  font-size: 0.7rem; 
  margin-top: 0.25rem; 
}

@media (max-width: 992px) {
  .details-grid { grid-template-columns: 1fr; gap: 2rem; }
  .photo-gallery { grid-template-columns: repeat(3, 1fr); }
  .evaluation-content-grid { grid-template-columns: 1fr; gap: 2.5rem; }
}
</style>

